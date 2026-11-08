<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Activitylog\Models\Activity;

class AuditTrailDemoSeeder extends Seeder
{
    /** Log name that marks these rows as sample data, so re-seeding replaces them. */
    public const LOG_NAME = 'demo';

    /** Real-world browser strings so the detail page shows how they are summarised. */
    private const USER_AGENTS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 15; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0',
    ];

    /**
     * Local/testing only: fill the audit trail with sample sign-ins and changes made by the seeded users.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $users = User::query()->orderBy('id')->get();

        if ($users->isEmpty()) {
            return;
        }

        Activity::query()->where('log_name', self::LOG_NAME)->delete();

        $moment = now()->subMinutes(7);

        foreach (range(0, 59) as $step) {
            $causer = $users[($step * 7) % $users->count()];
            $subject = $users->except($causer->getKey())->values()[($step * 3) % max(1, $users->count() - 1)] ?? $causer;

            $logger = activity(self::LOG_NAME)->causedBy($causer)->createdAt($moment->copy());

            match ($step % 6) {
                0 => $logger->event('login')->withProperties(['ip' => '10.0.0.'.(20 + $step), 'user_agent' => self::USER_AGENTS[$step % count(self::USER_AGENTS)]])->log('Signed in'),
                1 => $logger->event('updated')->performedOn($causer)
                    ->withChanges(['attributes' => ['name' => $causer->name], 'old' => ['name' => strtok($causer->name, ' ')]])
                    ->log('Updated their profile'),
                2 => $logger->event('updated')->performedOn($subject)
                    ->withChanges(['attributes' => ['email' => $subject->email, 'email_verified_at' => null], 'old' => ['email' => 'old.'.$subject->email, 'email_verified_at' => null]])
                    ->log("Updated {$subject->name}'s account"),
                3 => $logger->event('created')->performedOn($subject)->log("Assigned a role to {$subject->name}"),
                4 => $logger->event('logout')->log('Signed out'),
                default => $step % 12 === 5
                    ? $logger->event('deleted')->performedOn($subject)->log("Revoked a role from {$subject->name}")
                    : $logger->event('updated')->performedOn($causer)
                        ->withChanges(['attributes' => ['password' => '[hidden]'], 'old' => ['password' => '[hidden]']])
                        ->log('Changed their password'),
            };

            $moment = $moment->subMinutes(37 + ($step * 53) % 290);
        }
    }
}
