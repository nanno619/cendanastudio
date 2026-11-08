<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditTrailController extends Controller
{
    /**
     * List recorded activity, newest first, filtered by keyword, event, user and date range.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'event' => ['nullable', 'string', 'max:50'],
            'user' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $timezone = config('cendana.timezone');
        $userMorph = (new User)->getMorphClass();

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->where(
                fn (Builder $query) => $query
                    ->where('description', 'like', "%{$keyword}%")
                    ->orWhereHasMorph('causer', [User::class], fn (Builder $causer) => $causer
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")),
            ))
            ->when($filters['event'] ?? null, fn (Builder $query, string $event) => $query->where('event', $event))
            ->when($filters['user'] ?? null, fn (Builder $query, int|string $userId) => $query
                ->where('causer_type', $userMorph)
                ->where('causer_id', $userId))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query
                ->where('created_at', '>=', Carbon::parse($from, $timezone)->startOfDay()->utc()))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query
                ->where('created_at', '<=', Carbon::parse($to, $timezone)->endOfDay()->utc()))
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('administrations.audit-trail.index', [
            'activities' => $activities,
            'filters' => $filters,
            'timezone' => $timezone,
            'events' => Activity::query()->whereNotNull('event')->distinct()->orderBy('event')->pluck('event'),
            'causers' => User::query()
                ->whereIn('id', Activity::query()->where('causer_type', $userMorph)->select('causer_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
