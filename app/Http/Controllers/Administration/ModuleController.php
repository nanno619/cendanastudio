<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /**
     * List every module in sidebar order with its status and who can use it.
     */
    public function __invoke(): View
    {
        return view('administrations.modules.index', [
            'modules' => Module::registry(),
        ]);
    }
}
