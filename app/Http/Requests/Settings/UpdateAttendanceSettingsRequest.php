<?php

namespace App\Http\Requests\Settings;

use App\Enums\MissedClockOut;
use App\Enums\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates Settings → Hadir → General. The office location and radius are needed only while the
 * geofence is on, and the geofence needs locations to be recorded.
 */
class UpdateAttendanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $whileGeofenced = ['exclude_unless:geofence_enabled,1', 'required', 'numeric'];

        return [
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i', 'after:work_start_time'],
            'break_minutes' => ['required', 'integer', 'between:0,180'],
            'late_grace_minutes' => ['required', 'integer', 'between:0,60'],
            'missed_clock_out' => ['required', Rule::enum(MissedClockOut::class)],
            'record_location' => ['required', 'boolean', 'accepted_if:geofence_enabled,1'],
            'geofence_enabled' => ['required', 'boolean'],
            'office_latitude' => [...$whileGeofenced, 'decimal:0,6', 'between:-90,90'],
            'office_longitude' => [...$whileGeofenced, 'decimal:0,6', 'between:-180,180'],
            'geofence_radius_m' => [...$whileGeofenced, 'integer', 'between:50,5000'],
            'require_selfie' => ['required', 'boolean'],
        ];
    }

    /**
     * The break must leave some working time in the day.
     *
     * @return list<Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['work_start_time', 'work_end_time', 'break_minutes'])) {
                    return;
                }

                $dayMinutes = Carbon::createFromFormat('H:i', $this->input('work_start_time'))
                    ->diffInMinutes(Carbon::createFromFormat('H:i', $this->input('work_end_time')));

                if ((int) $this->input('break_minutes') >= $dayMinutes) {
                    $validator->errors()->add('break_minutes', __('The break must be shorter than the work day.'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'work_end_time.after' => __('The end time must be after the start time.'),
            'record_location.accepted_if' => __('Recording location must be on while the geofence is on.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'work_start_time' => __('Work starts'),
            'work_end_time' => __('Work ends'),
            'break_minutes' => __('Break'),
            'late_grace_minutes' => __('Late after'),
            'missed_clock_out' => __('When staff forget to clock out'),
            'record_location' => __('Record location'),
            'geofence_enabled' => __('Geofence'),
            'office_latitude' => __('Office latitude'),
            'office_longitude' => __('Office longitude'),
            'geofence_radius_m' => __('Radius'),
            'require_selfie' => __('Selfie when clocking in'),
        ];
    }
}
