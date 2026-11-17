<?php

namespace App\Http\Controllers\Staff;

use App\Enums\EmployeeStatus;
use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LookupItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * List staff by name, filtered by keyword, department and status. Without a status filter, staff who
     * have left are hidden.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(EmployeeStatus::class)],
        ]);

        $employees = Employee::query()
            ->with('user:id,name,email')
            ->withStatus(isset($filters['status']) ? EmployeeStatus::from($filters['status']) : null)
            ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->where(
                fn (Builder $query) => $query
                    ->where('staff_no', 'like', "%{$keyword}%")
                    ->orWhere('identity_no', 'like', '%'.preg_replace('/[\s-]/', '', $keyword).'%')
                    ->orWhereHas('user', fn (Builder $user) => $user
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")),
            ))
            ->when($filters['department'] ?? null, fn (Builder $query, string $department) => $query->where('department', $department))
            ->orderBy(fn ($query) => $query->select('name')->from('users')->whereColumn('users.id', 'employees.user_id'))
            ->paginate(20)
            ->withQueryString();

        return view('staff.index', [
            'employees' => $employees,
            'filters' => $filters,
            'departments' => LookupItem::labels(LookupCategory::Department),
            'positions' => LookupItem::labels(LookupCategory::Position),
            'statuses' => collect(EmployeeStatus::cases())->mapWithKeys(fn (EmployeeStatus $status): array => [$status->value => __($status->label())])->all(),
        ]);
    }
}
