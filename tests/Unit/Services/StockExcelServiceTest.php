<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private function tmp(string $binary): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xl');
        file_put_contents($path, $binary);

        return $path;
    }

    public function test_export_round_trips_through_parse(): void
    {
        $service = new StockExcelService;
        Stock::factory()->create(['name' => 'Roundtrip', 'sku' => 'RT-1', 'quantity' => 7, 'unit' => 'box', 'price' => 1234.5, 'min_stock' => 3]);

        $path = $this->tmp($service->export(Stock::all()));
        $sheet = IOFactory::load($path)->getActiveSheet();
        $this->assertSame('Roundtrip', $sheet->getCell('A2')->getValue());
        $this->assertSame('available', $sheet->getCell('H2')->getValue());

        $result = $service->parse($path);
        $this->assertSame([], $result['errors']);
        $this->assertSame('Roundtrip', $result['rows'][0]['name']);
        $this->assertSame('7', $result['rows'][0]['quantity']);
        unlink($path);
    }

    public function test_template_is_parseable(): void
    {
        $service = new StockExcelService;
        $path = $this->tmp($service->template());

        $result = $service->parse($path);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['rows']);
        unlink($path);
    }

    public function test_parse_skips_blank_rows_and_pads_short_rows(): void
    {
        $service = new StockExcelService;
        $sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet->getActiveSheet()->fromArray([['Name', 'Quantity', 'Min Stock'], ['A', 3], [null, null, null], ['B', 4, 1]], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'xl');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet))->save($path);

        $result = $service->parse($path);

        $this->assertSame([], $result['errors']);
        $this->assertCount(2, $result['rows']);
        unlink($path);
    }

    public function test_parse_reports_invalid_rows_with_line_numbers(): void
    {
        $service = new StockExcelService;
        $sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet->getActiveSheet()->fromArray([['name', 'quantity'], ['Ok', 1], ['', 2], ['X', 'banyak']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'xl');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet))->save($path);

        $result = $service->parse($path);

        $this->assertSame([], $result['rows']);
        $this->assertCount(2, $result['errors']);
        $this->assertStringStartsWith('Baris 3:', $result['errors'][0]);
        $this->assertStringStartsWith('Baris 4:', $result['errors'][1]);
        unlink($path);
    }

    public function test_parse_unreadable_file(): void
    {
        $path = $this->tmp('bukan excel');

        $result = (new StockExcelService)->parse($path);

        $this->assertSame([], $result['rows']);
        $this->assertStringContainsString('tidak dapat dibaca', $result['errors'][0]);
        unlink($path);
    }
}
