<?php

namespace App\Http\Controllers\Administration;

use App\Enums\FileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\UpdateFileSecurityRequest;
use App\Models\FileSecuritySetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class FileSecurityController extends Controller
{
    /**
     * Show the upload policy form, the always-blocked extensions and who last changed the policy.
     */
    public function __invoke(): View
    {
        $setting = FileSecuritySetting::current();

        return view('administrations.file-security.index', [
            'setting' => $setting,
            'types' => FileType::cases(),
            'blockedExtensions' => config('media-library.disallowed_extensions', []),
            'serverLimitMb' => UpdateFileSecurityRequest::serverLimitMb(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
