<?php

namespace App\Models;

use App\Enums\PayslipIdentityDisplay;
use Database\Factories\PayslipSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * What payslips show and how they reach staff (Settings → Payroll → Payslip). One row only; use
 * current(), which holds the defaults until the settings are first saved. Company details and employer
 * numbers come from Settings → Company.
 *
 * @property int $id
 * @property string $ulid
 * @property bool $show_company_logo
 * @property bool $show_employer_numbers EPF, SOCSO, EIS and income tax employer numbers
 * @property PayslipIdentityDisplay $identity_display IC or passport number
 * @property bool $mask_bank_account Only the last 4 digits of the staff member's account are shown
 * @property bool $show_employer_contributions Employer EPF, SOCSO and EIS
 * @property bool $show_year_to_date Year-to-date gross pay, EPF and PCB
 * @property bool $show_leave_balance
 * @property string $language A key of config('cendana.locales')
 * @property string|null $footer_note
 * @property bool $email_on_release Each staff member is emailed their payslip when a payroll month is released
 * @property bool $password_protect Emailed PDFs open with the staff member's IC or passport number
 */
class PayslipSetting extends Model
{
    /** @use HasFactory<PayslipSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    public const int FOOTER_NOTE_MAX_LENGTH = 500;

    /**
     * Digits left visible when a number is masked.
     */
    private const int VISIBLE_DIGITS = 4;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'show_company_logo', 'show_employer_numbers', 'identity_display', 'mask_bank_account',
        'show_employer_contributions', 'show_year_to_date', 'show_leave_balance',
        'language', 'footer_note', 'email_on_release', 'password_protect',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'show_company_logo' => 'boolean',
            'show_employer_numbers' => 'boolean',
            'identity_display' => PayslipIdentityDisplay::class,
            'mask_bank_account' => 'boolean',
            'show_employer_contributions' => 'boolean',
            'show_year_to_date' => 'boolean',
            'show_leave_balance' => 'boolean',
            'email_on_release' => 'boolean',
            'password_protect' => 'boolean',
        ];
    }

    /**
     * The saved settings, or an unsaved row holding the defaults.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static(static::defaults());
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'show_company_logo' => true,
            'show_employer_numbers' => true,
            'identity_display' => PayslipIdentityDisplay::Masked,
            'mask_bank_account' => true,
            'show_employer_contributions' => true,
            'show_year_to_date' => true,
            'show_leave_balance' => false,
            'language' => 'en',
            'footer_note' => null,
            'email_on_release' => true,
            'password_protect' => false,
        ];
    }

    /**
     * The IC or passport number as it should be printed, or null when it's hidden.
     * Masking keeps separators and the last 4 digits: 900101-14-5678 → XXXXXX-XX-5678.
     */
    public function displayIdentity(string $number): ?string
    {
        return match ($this->identity_display) {
            PayslipIdentityDisplay::Full => $number,
            PayslipIdentityDisplay::Masked => self::mask($number, 'X'),
            PayslipIdentityDisplay::Hidden => null,
        };
    }

    /**
     * The staff member's bank account number as it should be printed.
     */
    public function displayBankAccount(string $number): string
    {
        return $this->mask_bank_account ? self::mask($number, '*') : $number;
    }

    /**
     * Replace every letter or digit except the last 4 with the given character.
     */
    private static function mask(string $number, string $with): string
    {
        $characters = mb_str_split($number);
        $toKeep = self::VISIBLE_DIGITS;

        for ($i = count($characters) - 1; $i >= 0; $i--) {
            if (! ctype_alnum($characters[$i])) {
                continue;
            }

            if ($toKeep > 0) {
                $toKeep--;

                continue;
            }

            $characters[$i] = $with;
        }

        return implode('', $characters);
    }

    /**
     * The ULID is the public identifier; `id` stays the incrementing primary key.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (): string => 'Updated payslip settings');
    }
}
