<?php

namespace App\Http\Requests\Administration;

use App\Enums\FileType;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the upload policy saved on Administrations → File Security.
 */
class UpdateFileSecurityRequest extends FormRequest
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
        return [
            'allowed_types' => ['required', 'array', 'min:1'],
            'allowed_types.*' => ['required', 'distinct', Rule::enum(FileType::class)],
            'max_file_size_mb' => ['required', 'integer', 'min:1', 'max:'.static::serverLimitMb()],
            'verify_content' => ['required', 'boolean'],
        ];
    }

    /**
     * The largest upload PHP accepts, in whole MB (the smaller of upload_max_filesize and post_max_size).
     */
    public static function serverLimitMb(): int
    {
        $limits = array_filter([
            ini_parse_quantity((string) ini_get('upload_max_filesize')),
            ini_parse_quantity((string) ini_get('post_max_size')),
        ], fn (int $bytes): bool => $bytes > 0);

        return min(65535, max(1, intdiv($limits === [] ? PHP_INT_MAX : min($limits), 1024 * 1024)));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'allowed_types.required' => __('Allow at least one file type.'),
            'max_file_size_mb.max' => __('The server accepts uploads up to :max MB.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'allowed_types' => __('Allowed file types'),
            'allowed_types.*' => __('File type'),
            'max_file_size_mb' => __('Maximum file size'),
            'verify_content' => __('Check file content'),
        ];
    }
}
