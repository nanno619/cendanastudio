<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditTrailEntryController extends Controller
{
    /** Fields whose values are never shown, only that they changed. */
    private const HIDDEN_FIELDS = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    /**
     * Show one audit trail entry: who, what, when, the field-level changes and related activity.
     */
    public function __invoke(Request $request, Activity $activity): View
    {
        $activity->load(['causer', 'subject']);

        $listUrl = route('administrations.audit-trail.index');
        $previous = url()->previous();

        return view('administrations.audit-trail.show', [
            'activity' => $activity,
            'timezone' => config('cendana.timezone'),
            'changes' => $this->changes($activity),
            'backUrl' => Str::startsWith($previous, $listUrl) && ! Str::startsWith($previous, $listUrl.'/') ? $previous : $listUrl,
            'related' => $this->related($activity),
        ]);
    }

    /**
     * Pair old and new values per field from the activity's attribute changes.
     *
     * @return list<array{field: string, old: mixed, new: mixed, hasOld: bool, hidden: bool}>
     */
    private function changes(Activity $activity): array
    {
        $new = (array) ($activity->attribute_changes?->get('attributes') ?? []);
        $old = (array) ($activity->attribute_changes?->get('old') ?? []);

        return collect(array_keys($new + $old))
            ->map(fn (string $field): array => [
                'field' => $field,
                'old' => $old[$field] ?? null,
                'new' => $new[$field] ?? null,
                'hasOld' => array_key_exists($field, $old),
                'hidden' => in_array($field, self::HIDDEN_FIELDS, true),
            ])
            ->all();
    }

    /**
     * Other recent activity on the same subject, or by the same user when there is no subject.
     *
     * @return Collection<int, Activity>
     */
    private function related(Activity $activity): Collection
    {
        $query = Activity::query()->with('causer')->whereKeyNot($activity->getKey())->latest()->latest('id')->limit(5);

        if ($activity->subject_type) {
            return $query->where('subject_type', $activity->subject_type)->where('subject_id', $activity->subject_id)->get();
        }

        if ($activity->causer_type) {
            return $query->where('causer_type', $activity->causer_type)->where('causer_id', $activity->causer_id)->get();
        }

        return $activity->newCollection();
    }
}
