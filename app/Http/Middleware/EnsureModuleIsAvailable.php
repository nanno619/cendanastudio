<?php

namespace App\Http\Middleware;

use App\Enums\ModuleKey;
use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `module:<key>`: 404 when the module is off (as if its pages didn't exist), 403 when it is limited to
 * roles the user doesn't hold.
 */
class EnsureModuleIsAvailable
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key): Response
    {
        $module = Module::for(ModuleKey::from($key));

        abort_unless($module->is_enabled, 404);
        abort_unless($module->isAvailableTo($request->user()), 403);

        return $next($request);
    }
}
