<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->member = User::factory()->create();
    }

    private function lw()
    {
        return Livewire::actingAs($this->member)->test(StockList::class);
    }

    private function xlsx(array $rows): UploadedFile
    {
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray($rows, null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'x');
        (new Xlsx($sheet))->save($path);
        $content = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent('stok.xlsx', $content);
    }

    public function test_lists_only_own_stocks(): void
    {
        Stock::factory()->for($this->member)->create(['name' => 'Milik Saya']);
        Stock::factory()->create(['name' => 'Milik Orang Lain']);

        $this->lw()->assertSee('Milik Saya')->assertDontSee('Milik Orang Lain');
    }

    public function test_search_and_status_filter_and_pagination_reset(): void
    {
        Stock::factory()->for($this->member)->create(['name' => 'Kabel USB', 'sku' => 'KBL-1', 'quantity' => 50]);
        Stock::factory()->for($this->member)->create(['name' => 'Mouse', 'sku' => 'MSE-1', 'quantity' => 0]);

        $this->lw()
            ->set('search', 'kabel')->assertSee('Kabel USB')->assertDontSee('Mouse')
            ->set('search', 'MSE-1')->assertSee('Mouse')
            ->set('search', '')->set('statusFilter', 'out_of_stock')->assertSee('Mouse')->assertDontSee('Kabel USB');
    }

    public function test_create_stock_with_auto_status_and_cover(): void
    {
        $this->lw()
            ->call('create')
            ->set('name', 'Lakban')->set('sku', 'LKB-1')->set('description', '**tebal**')
            ->set('quantity', '3')->set('unit', 'roll')->set('price', '12500.5')->set('min_stock', '5')
            ->set('cover', UploadedFile::fake()->image('cover.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $stock = Stock::where('name', 'Lakban')->firstOrFail();
        $this->assertSame($this->member->id, $stock->user_id);
        $this->assertSame('low_stock', $stock->status->value);
        $this->assertSame('12500.50', $stock->price);
        Storage::disk('public')->assertExists($stock->cover_image_path);
    }

    public function test_create_validation_errors(): void
    {
        $this->lw()->call('create')
            ->set('name', '')->set('quantity', '-1')->set('min_stock', 'abc')
            ->call('save')
            ->assertHasErrors(['name', 'quantity', 'min_stock']);
    }

    public function test_edit_updates_stock_status_and_replaces_cover(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['quantity' => 50, 'min_stock' => 5, 'cover_image_path' => 'stock-covers/old.jpg']);
        Storage::disk('public')->put('stock-covers/old.jpg', 'x');

        $this->lw()
            ->call('edit', $stock->id)
            ->assertSet('name', $stock->name)->assertSet('showForm', true)
            ->set('quantity', '0')
            ->set('price', '')
            ->set('cover', UploadedFile::fake()->image('new.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $stock->refresh();
        $this->assertSame('out_of_stock', $stock->status->value);
        $this->assertNull($stock->price);
        Storage::disk('public')->assertMissing('stock-covers/old.jpg');
        Storage::disk('public')->assertExists($stock->cover_image_path);
    }

    public function test_edit_form_handles_null_price(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['price' => null, 'sku' => null, 'description' => null]);

        $this->lw()->call('edit', $stock->id)->assertSet('price', '')->assertSet('sku', '');
    }

    public function test_close_form_resets_state(): void
    {
        $this->lw()->call('create')->set('name', 'X')->call('closeForm')->assertSet('showForm', false)->assertSet('name', '');
    }

    public function test_delete_removes_stock_and_cover(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['cover_image_path' => 'stock-covers/a.jpg']);
        Storage::disk('public')->put('stock-covers/a.jpg', 'x');

        $this->lw()->call('delete', $stock->id);

        $this->assertModelMissing($stock);
        Storage::disk('public')->assertMissing('stock-covers/a.jpg');
    }

    public function test_adjust_quantity_updates_status_and_prevents_negative(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['quantity' => 6, 'min_stock' => 5]);

        $this->lw()->call('adjust', $stock->id, -1);
        $this->assertSame('low_stock', $stock->refresh()->status->value);

        $this->lw()->call('adjust', $stock->id, 10);
        $this->assertSame('available', $stock->refresh()->status->value);

        $this->lw()->call('adjust', $stock->id, -16)->assertHasErrors(['adjust']);
        $this->assertSame(15, $stock->refresh()->quantity);

        $this->lw()->call('adjust', $stock->id, -15);
        $this->assertSame('out_of_stock', $stock->refresh()->status->value);
    }

    public function test_cannot_touch_other_users_stock(): void
    {
        $other = Stock::factory()->create();

        $this->lw()->call('edit', $other->id)->assertForbidden();
        $this->lw()->call('delete', $other->id)->assertForbidden();
        $this->lw()->call('adjust', $other->id, 1)->assertForbidden();
        $this->assertDatabaseHas('stocks', ['id' => $other->id, 'quantity' => $other->quantity]);
    }

    public function test_unknown_stock_is_404(): void
    {
        $this->lw()->call('edit', 999999)->assertStatus(404);
    }

    public function test_cannot_save_edit_of_other_users_stock(): void
    {
        $other = Stock::factory()->create(['name' => 'Asli']);

        $this->lw()->set('editingId', $other->id)->set('name', 'Dibajak')->set('quantity', '1')->set('unit', 'pcs')->set('min_stock', '1')
            ->call('save')->assertForbidden();
        $this->assertSame('Asli', $other->refresh()->name);
    }

    public function test_admin_cannot_create_via_component(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())->test(StockList::class)->call('create')->assertForbidden();
    }

    public function test_admin_cannot_save_new_stock_via_component(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())->test(StockList::class)
            ->set('name', 'X')->set('quantity', '1')->set('unit', 'pcs')->set('min_stock', '1')
            ->call('save')->assertForbidden();
        $this->assertDatabaseCount('stocks', 0);
    }

    public function test_export_and_template_download(): void
    {
        Stock::factory()->for($this->member)->count(2)->create();

        $this->lw()->call('export')->assertFileDownloaded();
        $this->lw()->call('downloadTemplate')->assertFileDownloaded('template-stok.xlsx');
    }

    public function test_import_valid_file_creates_stocks(): void
    {
        $file = $this->xlsx([
            ['name', 'sku', 'description', 'quantity', 'unit', 'price', 'min_stock', 'status'],
            ['Baterai AA', 'BAT-1', 'Alkaline', 2, 'pak', 15000, 5, ''],
            ['Lem', null, null, 40, null, null, null, null],
        ]);

        $this->lw()->set('importFile', $file)->call('import')
            ->assertHasNoErrors()->assertSet('importErrors', [])->assertSee('2 stok berhasil diimpor');

        $this->assertSame('low_stock', Stock::where('name', 'Baterai AA')->first()->status->value);
        $this->assertSame('pcs', Stock::where('name', 'Lem')->first()->unit);
    }

    public function test_import_invalid_rows_saves_nothing_and_reports_row_numbers(): void
    {
        $file = $this->xlsx([
            ['name', 'quantity', 'price'],
            ['Valid', 5, 100],
            ['', 5, 100],
            ['Negatif', -3, 'abc'],
        ]);

        $component = $this->lw()->set('importFile', $file)->call('import');

        $errors = $component->get('importErrors');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Baris 3', implode(' ', $errors));
        $this->assertStringContainsString('Baris 4', implode(' ', $errors));
        $this->assertDatabaseCount('stocks', 0);
    }

    public function test_import_requires_file(): void
    {
        $this->lw()->call('import')->assertHasErrors(['importFile']);
    }

    public function test_import_corrupt_file_reports_error(): void
    {
        $file = UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan excel');

        $this->lw()->set('importFile', $file)->call('import')->assertSet('importErrors', ['File Excel tidak dapat dibaca. Gunakan file .xlsx atau .xls yang valid.']);
    }

    public function test_import_missing_required_columns(): void
    {
        $file = $this->xlsx([['sku'], ['A']]);

        $this->lw()->set('importFile', $file)->call('import')->assertSet('importErrors', ['Kolom wajib tidak ditemukan: name, quantity.']);
    }

    public function test_import_empty_data_file(): void
    {
        $file = $this->xlsx([['name', 'quantity']]);

        $this->lw()->set('importFile', $file)->call('import')->assertSet('importErrors', ['File tidak berisi data stok.']);
    }

    public function test_ai_generate_preview_select_and_save(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willReturn([
            new GeneratedStockItem('Kursi', 'KRS-1', 'Kursi kerja', 4, 'unit', 750000.0, 5),
            new GeneratedStockItem('Meja', null, null, 20, 'unit', null, 5),
        ]);
        $this->app->instance(AiClientInterface::class, $stub);

        $this->lw()
            ->call('openAi')->assertSet('showAi', true)
            ->set('aiPrompt', 'perabot kantor')->call('generateAi')
            ->assertSet('aiError', null)->assertSee('Kursi')->assertSee('Meja')
            ->set('aiSelected', ['0'])
            ->call('saveAiItems')
            ->assertSet('showAi', false);

        $this->assertDatabaseHas('stocks', ['name' => 'Kursi', 'user_id' => $this->member->id, 'status' => 'low_stock']);
        $this->assertDatabaseMissing('stocks', ['name' => 'Meja']);
    }

    public function test_ai_save_without_selection_shows_error(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willReturn([new GeneratedStockItem('Kursi', null, null, 4, 'unit', null, 5)]);
        $this->app->instance(AiClientInterface::class, $stub);

        $this->lw()->set('aiPrompt', 'perabot')->call('generateAi')
            ->set('aiSelected', [])->call('saveAiItems')
            ->assertSet('aiError', 'Pilih minimal satu item untuk disimpan.');
        $this->assertDatabaseCount('stocks', 0);
    }

    public function test_ai_failure_is_handled_gracefully(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willThrowException(new RuntimeException('API down'));
        $this->app->instance(AiClientInterface::class, $stub);

        $this->lw()->set('aiPrompt', 'perabot')->call('generateAi')
            ->assertSet('aiError', 'API down')->assertSet('aiItems', [])->assertOk();
    }

    public function test_ai_without_api_key_shows_configuration_message(): void
    {
        config(['openai.api_key' => '']);

        $this->lw()->call('openAi')->set('aiPrompt', 'perabot')->call('generateAi')
            ->assertSee('OPENAI_API_KEY belum dikonfigurasi');
    }

    public function test_ai_prompt_validation(): void
    {
        $this->lw()->set('aiPrompt', '')->call('generateAi')->assertHasErrors(['aiPrompt']);
        $this->lw()->set('aiPrompt', 'ok ok')->set('aiCount', '99')->call('generateAi')->assertHasErrors(['aiCount']);
        $this->lw()->call('closeAi')->assertSet('showAi', false);
    }
}