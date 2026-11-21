<?php

namespace App\Http\Requests\Projects;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates adding (POST) or editing (PUT) a project. Super Admin and Director choose the project manager;
 * a Project Manager manages the projects they add and can't hand them to someone else.
 */
class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return $this->project()?->isEditableBy($user) ?? Project::isCreatableBy($user);
    }

    public function project(): ?Project
    {
        $project = $this->route('project');

        return $project instanceof Project ? $project : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => filled($this->input('code')) ? strtoupper(preg_replace('/\s+/', '', (string) $this->input('code'))) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9-]+$/', Rule::unique('projects', 'code')->ignore($this->project()?->id)],
            'name' => ['required', 'string', 'max:200'],
            'client' => ['required', 'string', Rule::exists('clients', 'ulid')],
            'project_manager' => [Rule::excludeIf(! Project::canAssignManager($this->user())), 'nullable', 'string', Rule::exists('employees', 'ulid')],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * A Project Manager adding a project needs a staff record to become its manager.
     *
     * @return list<Closure>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->project() === null && ! Project::canAssignManager($this->user()) && $this->user()->employee === null) {
                $validator->errors()->add('project_manager', __('You need a staff record to manage a project.'));
            }
        }];
    }

    /**
     * The project's fields with the client and manager resolved from their ULIDs.
     *
     * @return array<string, mixed>
     */
    public function projectAttributes(): array
    {
        $data = collect($this->validated())->except(['client', 'project_manager'])->all();
        $data['client_id'] = Client::query()->where('ulid', $this->validated('client'))->value('id');

        if (Project::canAssignManager($this->user())) {
            $data['project_manager_id'] = filled($this->validated('project_manager'))
                ? Employee::query()->where('ulid', $this->validated('project_manager'))->value('id')
                : null;
        } elseif ($this->project() === null) {
            $data['project_manager_id'] = $this->user()->employee?->id;
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => __('Use capital letters, numbers and dashes only, e.g. PRJ-2026-001.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => __('Project code'),
            'name' => __('Project name'),
            'client' => __('Client'),
            'project_manager' => __('Project manager'),
            'status' => __('Status'),
            'start_date' => __('Start date'),
            'end_date' => __('End date'),
            'description' => __('Description'),
        ];
    }
}
