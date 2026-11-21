@php
    $money = fn (float $amount): string => 'RM '.number_format($amount, 2);
    $gross = collect($payslip['earnings'])->sum('amount');
    $totalDeductions = collect($payslip['deductions'])->sum('amount');
    $identity = $setting->displayIdentity($payslip['identity']);
    $address = collect([$company->address_line_1, $company->address_line_2, trim($company->postcode.' '.$company->city), $companyState])->filter()->implode(', ');
    $employerNumbers = collect([
        __('EPF') => $company->epf_no,
        __('SOCSO') => $company->socso_no,
        __('EIS') => $company->eis_no,
        __('Income tax') => $company->income_tax_no,
    ])->filter();
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Payslip') }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 15px; margin: 0; }
        h2 { font-size: 11px; margin: 0 0 6px; text-transform: uppercase; letter-spacing: 0.04em; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        .header td { vertical-align: top; }
        .logo { max-height: 48px; max-width: 160px; }
        .title { font-size: 18px; font-weight: bold; }
        .box { border: 1px solid #e5e7eb; padding: 10px; margin-top: 12px; }
        .lines td { padding: 3px 0; border-bottom: 1px solid #f3f4f6; }
        .lines tr.total td { font-weight: bold; border-bottom: 0; border-top: 1px solid #d1d5db; padding-top: 5px; }
        .details td { padding: 2px 0; width: 25%; }
        .net { background: #f3f4f6; padding: 10px; margin-top: 12px; font-size: 13px; font-weight: bold; }
        .columns td.col { width: 50%; vertical-align: top; }
        .columns td.col:first-child { padding-right: 8px; }
        .columns td.col:last-child { padding-left: 8px; }
        .footer { margin-top: 16px; font-size: 9px; }
        .preview { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                @if ($logo)
                    <img src="{{ $logo }}" class="logo" alt=""><br>
                @endif
                <h1>{{ $companyName }}</h1>
                @if ($company->registration_no)
                    <div class="muted">{{ $company->registration_no }}@if ($company->old_registration_no) ({{ $company->old_registration_no }})@endif</div>
                @endif
                @if ($address)
                    <div class="muted">{{ $address }}</div>
                @endif
            </td>
            <td class="right">
                <div class="title">{{ __('Payslip') }}</div>
                <div>{{ $payslip['period']->locale(app()->getLocale())->translatedFormat('F Y') }}</div>
                <div class="preview">{{ __('Sample preview') }}</div>
            </td>
        </tr>
    </table>

    @if ($setting->show_employer_numbers && $employerNumbers->isNotEmpty())
        <p class="muted">{{ __('Employer numbers') }}: {{ $employerNumbers->map(fn ($number, $label) => $label.' '.$number)->implode(' · ') }}</p>
    @endif

    <div class="box">
        <table class="details">
            <tr>
                <td class="muted">{{ __('Name') }}</td><td>{{ $payslip['name'] }}</td>
                <td class="muted">{{ __('Employee number') }}</td><td>{{ $payslip['staff_no'] }}</td>
            </tr>
            <tr>
                <td class="muted">{{ __('Position') }}</td><td>{{ $payslip['position'] }}</td>
                <td class="muted">{{ __('Department') }}</td><td>{{ $payslip['department'] }}</td>
            </tr>
            <tr>
                @if ($identity)
                    <td class="muted">{{ __('IC or passport number') }}</td><td>{{ $identity }}</td>
                @else
                    <td></td><td></td>
                @endif
                <td class="muted">{{ __('Bank account') }}</td><td>{{ $payslip['bank'] }} {{ $setting->displayBankAccount($payslip['bank_account']) }}</td>
            </tr>
            <tr>
                <td class="muted">{{ __('EPF number') }}</td><td>{{ $payslip['epf_no'] }}</td>
                <td class="muted">{{ __('Income tax number') }}</td><td>{{ $payslip['tax_no'] }}</td>
            </tr>
        </table>
    </div>

    <table class="columns" style="margin-top: 12px;">
        <tr>
            <td class="col">
                <h2>{{ __('Earnings') }}</h2>
                <table class="lines">
                    @foreach ($payslip['earnings'] as $line)
                        <tr><td>{{ __($line['label']) }}</td><td class="right">{{ $money($line['amount']) }}</td></tr>
                    @endforeach
                    <tr class="total"><td>{{ __('Gross pay') }}</td><td class="right">{{ $money($gross) }}</td></tr>
                </table>
            </td>
            <td class="col">
                <h2>{{ __('Deductions') }}</h2>
                <table class="lines">
                    @foreach ($payslip['deductions'] as $line)
                        <tr><td>{{ __($line['label']) }}</td><td class="right">{{ $money($line['amount']) }}</td></tr>
                    @endforeach
                    <tr class="total"><td>{{ __('Total deductions') }}</td><td class="right">{{ $money($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="net">
        <tr><td>{{ __('Net pay') }}</td><td class="right">{{ $money($gross - $totalDeductions) }}</td></tr>
    </table>

    @if ($setting->show_employer_contributions || $setting->show_year_to_date)
        <table class="columns" style="margin-top: 12px;">
            <tr>
                <td class="col">
                    @if ($setting->show_employer_contributions)
                        <h2>{{ __('Employer contributions') }}</h2>
                        <table class="lines">
                            @foreach ($payslip['employer_contributions'] as $line)
                                <tr><td>{{ __($line['label']) }}</td><td class="right">{{ $money($line['amount']) }}</td></tr>
                            @endforeach
                        </table>
                    @endif
                </td>
                <td class="col">
                    @if ($setting->show_year_to_date)
                        <h2>{{ __('Year to date') }}</h2>
                        <table class="lines">
                            @foreach ($payslip['year_to_date'] as $line)
                                <tr><td>{{ __($line['label']) }}</td><td class="right">{{ $money($line['amount']) }}</td></tr>
                            @endforeach
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    @if ($setting->show_leave_balance)
        <div style="margin-top: 12px;">
            <h2>{{ __('Leave balance') }}</h2>
            <table class="lines">
                @foreach ($payslip['leave_balance'] as $line)
                    <tr><td>{{ __($line['label']) }}</td><td class="right">{{ __(':days days', ['days' => rtrim(rtrim(number_format($line['days'], 1), '0'), '.')]) }}</td></tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="footer muted">
        @if ($setting->footer_note)
            <p>{!! nl2br(e($setting->footer_note)) !!}</p>
        @endif
        <p>{{ __('This payslip is computer-generated and needs no signature.') }}</p>
    </div>
</body>
</html>
