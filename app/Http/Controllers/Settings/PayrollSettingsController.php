<?php

namespace App\Http\Controllers\Settings;

use App\Enums\HrdfLevy;
use App\Enums\NetPayRounding;
use App\Enums\PayDayAdjustment;
use App\Enums\PayDayRule;
use App\Enums\PaymentMethod;
use App\Enums\PcbMethod;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\PayrollSetting;
use BackedEnum;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class PayrollSettingsController extends Controller
{
    /**
     * Show the company-wide payroll rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = PayrollSetting::current();

        return view('settings.payroll.general', [
            'setting' => $setting,
            'payDayRules' => $this->options(PayDayRule::cases()),
            'adjustments' => $this->options(PayDayAdjustment::cases()),
            'pcbMethods' => $this->options(PcbMethod::cases()),
            'hrdfLevies' => $this->options(HrdfLevy::cases()),
            'paymentMethods' => $this->options(PaymentMethod::cases()),
            'roundings' => $this->options(NetPayRounding::cases()),
            'roles' => collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => __($role->value)])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }

    /**
     * Select options (value => translated label) for enum cases with a label().
     *
     * @param  list<BackedEnum>  $cases
     * @return array<string, string>
     */
    private function options(array $cases): array
    {
        return collect($cases)->mapWithKeys(fn (BackedEnum $case): array => [$case->value => __($case->label())])->all();
    }
}
