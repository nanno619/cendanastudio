<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentSecurityPolicyReportController extends Controller
{
    /**
     * Report fields worth keeping, as browsers send them in a `csp-report` body.
     *
     * @var list<string>
     */
    public const array FIELDS = [
        'document-uri',
        'effective-directive',
        'violated-directive',
        'blocked-uri',
        'source-file',
        'line-number',
        'disposition',
    ];

    /**
     * Log a browser's CSP violation report to the `csp` channel. Anyone can post here,
     * so only known fields are kept, each cut short, and nothing is echoed back.
     */
    public function __invoke(Request $request): Response
    {
        $report = json_decode($request->getContent(), true)['csp-report'] ?? null;

        if (is_array($report)) {
            $fields = collect($report)
                ->only(self::FIELDS)
                ->filter(fn (mixed $value): bool => is_scalar($value))
                ->map(fn (string|int|float|bool $value): string => Str::limit((string) $value, 300))
                ->all();

            if ($fields !== []) {
                Log::channel('csp')->warning('CSP violation', $fields);
            }
        }

        return response()->noContent();
    }
}
