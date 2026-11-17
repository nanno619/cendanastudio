<?php

namespace App\Http\Requests\Staff;

use App\Enums\DocumentType;
use App\Models\Employee;
use App\Rules\AllowedUpload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a document uploaded to a staff record. File types and size follow Administrations → File
 * Security through AllowedUpload.
 */
class UploadEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $employee instanceof Employee && $this->user() !== null && $employee->isEditableBy($this->user());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => ['required', 'file', new AllowedUpload],
            'type' => ['required', Rule::enum(DocumentType::class)],
            'note' => ['nullable', 'string', 'max:'.Employee::DOCUMENT_NOTE_MAX_LENGTH],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'document' => __('document'),
            'type' => __('Document type'),
            'note' => __('Note'),
        ];
    }
}
