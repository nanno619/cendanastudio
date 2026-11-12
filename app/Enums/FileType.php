<?php

namespace App\Enums;

/**
 * Groups of file types Super Admin can allow for uploads on the File Security page. Defined in code
 * because each group needs its extensions and the MIME types its content may be detected as.
 * Labels are English and translated with __() where shown.
 */
enum FileType: string
{
    case Pdf = 'pdf';
    case Image = 'image';
    case Word = 'word';
    case Excel = 'excel';
    case PowerPoint = 'powerpoint';
    case Text = 'text';
    case Archive = 'archive';

    /**
     * Office Open XML files are zip containers, so some systems detect them as plain zip.
     */
    private const OFFICE_ZIP = ['application/zip', 'application/x-zip-compressed'];

    /**
     * Legacy Office files share one container format that is often detected generically.
     */
    private const OFFICE_LEGACY = ['application/x-ole-storage', 'application/CDFV2'];

    public function label(): string
    {
        return match ($this) {
            self::Pdf => 'PDF documents',
            self::Image => 'Images',
            self::Word => 'Word documents',
            self::Excel => 'Spreadsheets',
            self::PowerPoint => 'Presentations',
            self::Text => 'Plain text',
            self::Archive => 'Zip archives',
        };
    }

    /**
     * @return list<string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::Pdf => ['pdf'],
            self::Image => ['jpg', 'jpeg', 'png', 'webp'],
            self::Word => ['doc', 'docx'],
            self::Excel => ['xls', 'xlsx', 'csv'],
            self::PowerPoint => ['ppt', 'pptx'],
            self::Text => ['txt'],
            self::Archive => ['zip'],
        };
    }

    /**
     * MIME types a file of this group may be detected as from its content.
     *
     * @return list<string>
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::Pdf => ['application/pdf'],
            self::Image => ['image/jpeg', 'image/png', 'image/webp'],
            self::Word => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', ...self::OFFICE_ZIP, ...self::OFFICE_LEGACY],
            self::Excel => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain', ...self::OFFICE_ZIP, ...self::OFFICE_LEGACY],
            self::PowerPoint => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', ...self::OFFICE_ZIP, ...self::OFFICE_LEGACY],
            self::Text => ['text/plain'],
            self::Archive => self::OFFICE_ZIP,
        };
    }

    /**
     * The group an extension belongs to, or null when no group lists it.
     */
    public static function forExtension(string $extension): ?self
    {
        $extension = strtolower($extension);

        foreach (self::cases() as $type) {
            if (in_array($extension, $type->extensions(), true)) {
                return $type;
            }
        }

        return null;
    }
}
