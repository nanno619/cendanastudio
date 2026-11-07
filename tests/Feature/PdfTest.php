<?php

namespace Tests\Feature;

use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class PdfTest extends TestCase
{
    public function test_renders_html_to_a_pdf_document(): void
    {
        $pdf = Pdf::loadHTML('<h1>Quote</h1><p>RM 1,250.00</p>')->output();

        $this->assertStringStartsWith('%PDF-', $pdf);
    }
}
