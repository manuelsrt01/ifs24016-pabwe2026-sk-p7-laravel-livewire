<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Requests\StockRules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class StockExcelService
{
    public const HEADERS = ['name', 'sku', 'description', 'quantity', 'unit', 'price', 'min_stock', 'status'];

    /** @param  iterable<\App\Features\Stock\Models\Stock>  $stocks  Mengembalikan isi file .xlsx. */
    public function export(iterable $stocks): string
    {
        $spreadsheet = $this->newSheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 2;

        foreach ($stocks as $stock) {
            $sheet->fromArray([
                $stock->name, $stock->sku, $stock->description, $stock->quantity,
                $stock->unit, $stock->price, $stock->min_stock, $stock->status->value,
            ], null, "A{$row}");
            $row++;
        }

        return $this->toBinary($spreadsheet);
    }

    /** Template kosong berisi header + satu contoh baris. */
    public function template(): string
    {
        $spreadsheet = $this->newSheet();
        $spreadsheet->getActiveSheet()->fromArray(
            ['Contoh Barang', 'SKU-001', 'Deskripsi (boleh Markdown)', 10, 'pcs', 15000, 5, 'available'],
            null,
            'A2'
        );

        return $this->toBinary($spreadsheet);
    }

    /**
     * Baca & validasi seluruh baris. Jika ada satu saja baris tidak valid, tidak ada data yang dikembalikan
     * untuk disimpan (all-or-nothing) dan pesan error menyebut nomor baris.
     *
     * @return array{rows: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    public function parse(string $path): array
    {
        try {
            $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
        } catch (Throwable) {
            return ['rows' => [], 'errors' => ['File Excel tidak dapat dibaca. Gunakan file .xlsx atau .xls yang valid.']];
        }

        $header = array_map(
            fn ($h) => Str::of((string) $h)->trim()->lower()->replace(' ', '_')->toString(),
            array_shift($rows) ?? []
        );

        $missing = array_diff(['name', 'quantity'], $header);
        if ($missing !== []) {
            return ['rows' => [], 'errors' => ['Kolom wajib tidak ditemukan: '.implode(', ', $missing).'.']];
        }

        $parsed = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $row = array_slice(array_pad($row, count($header), null), 0, count($header));
            $data = array_map(fn ($v) => is_scalar($v) ? (string) $v : $v, array_combine($header, $row));

            if (collect($data)->filter(fn ($v) => filled($v))->isEmpty()) {
                continue;
            }

            $validator = Validator::make($data, StockRules::importRules());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = 'Baris '.($index + 2).': '.$message;
                }

                continue;
            }

            $parsed[] = $data;
        }

        if ($parsed === [] && $errors === []) {
            $errors[] = 'File tidak berisi data stok.';
        }

        return ['rows' => $errors === [] ? $parsed : [], 'errors' => $errors];
    }

    private function newSheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->setTitle('Stok')->fromArray(self::HEADERS, null, 'A1');

        return $spreadsheet;
    }

    private function toBinary(Spreadsheet $spreadsheet): string
    {
        $path = tempnam(sys_get_temp_dir(), 'stock');
        (new Xlsx($spreadsheet))->save($path);
        $content = (string) file_get_contents($path);
        unlink($path);

        return $content;
    }
}
