<?php

namespace App\Rules;

use App\Enums\FileType;
use App\Models\FileSecuritySetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validates an upload against the File Security policy: allowed types, max size, always-blocked
 * extensions anywhere in the name, and (when enabled) that the content matches the extension.
 * Use as ['required', 'file', new AllowedUpload].
 */
class AllowedUpload implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail(__('The :attribute failed to upload.'));

            return;
        }

        $policy = FileSecuritySetting::current();
        $name = strtolower($value->getClientOriginalName());
        $segments = array_slice(explode('.', $name), 1);

        if (array_intersect($segments, $this->blockedExtensions()) !== []) {
            $fail(__('The :attribute has a file type that is never allowed.'));

            return;
        }

        $type = FileType::forExtension((string) end($segments));

        if ($segments === [] || $type === null || ! $policy->allows($type)) {
            $fail(__('The :attribute must be one of these file types: :types.', ['types' => implode(', ', $policy->allowedExtensions())]));

            return;
        }

        if ($value->getSize() > $policy->max_file_size_mb * 1024 * 1024) {
            $fail(__('The :attribute must not be larger than :size MB.', ['size' => $policy->max_file_size_mb]));

            return;
        }

        if ($policy->verify_content && ! in_array($value->getMimeType(), $type->mimeTypes(), true)) {
            $fail(__('The :attribute content does not match its file extension.'));
        }
    }

    /**
     * @return list<string>
     */
    private function blockedExtensions(): array
    {
        return array_map(fn (string $extension): string => strtolower(ltrim($extension, '.')), config('media-library.disallowed_extensions', []));
    }
}
