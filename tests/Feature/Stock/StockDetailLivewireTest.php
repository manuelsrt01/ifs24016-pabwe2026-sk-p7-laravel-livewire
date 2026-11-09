<?php

namespace Tests\Feature\Stock;

use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockDetailLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->member = User::factory()->create();
    }

    public function test_renders_details_and_markdown_safely(): void
    {
        $stock = Stock::factory()->for($this->member)->create([
            'description' => "# Judul\n\n**tebal** <script>alert(1)</script>",
            'cover_image_path' => 'stock-covers/c.jpg',
        ]);

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])
            ->assertSee($stock->name)->assertSee($stock->sku)->assertSeeHtml('<strong>tebal</strong>')
            ->assertDontSeeHtml('<script>alert(1)</script>')
            ->assertSeeHtml('stock-covers/c.jpg');
    }

    public function test_renders_without_price_and_cover(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['price' => null, 'sku' => null, 'description' => null]);

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])->assertOk();
    }

    public function test_other_user_cannot_mount(): void
    {
        $stock = Stock::factory()->create();

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])->assertForbidden();
    }

    public function test_edit_and_save(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['quantity' => 50, 'min_stock' => 5, 'price' => null, 'cover_image_path' => null]);

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])
            ->call('startEdit')->assertSet('editing', true)->assertSet('price', '')
            ->set('name', 'Nama Baru')->set('quantity', '2')->set('price', '9.99')
            ->set('cover', UploadedFile::fake()->image('c.png'))
            ->call('save')->assertHasNoErrors()->assertSet('editing', false);

        $stock->refresh();
        $this->assertSame('Nama Baru', $stock->name);
        $this->assertSame('low_stock', $stock->status->value);
        Storage::disk('public')->assertExists($stock->cover_image_path);
    }

    public function test_edit_with_existing_price_and_cancel(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['price' => 100]);

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])
            ->call('startEdit')->assertSet('price', '100.00')
            ->call('cancelEdit')->assertSet('editing', false);
    }

    public function test_save_validation(): void
    {
        $stock = Stock::factory()->for($this->member)->create();

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])
            ->call('startEdit')->set('name', '')->call('save')->assertHasErrors(['name']);
    }

    public function test_adjust_quantity_and_negative_guard(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['quantity' => 1, 'min_stock' => 5]);

        $c = Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock]);
        $c->call('adjust', -1);
        $this->assertSame('out_of_stock', $stock->refresh()->status->value);

        $c->call('adjust', -1)->assertHasErrors(['adjust']);
        $this->assertSame(0, $stock->refresh()->quantity);
    }

    public function test_delete_redirects_to_list(): void
    {
        $stock = Stock::factory()->for($this->member)->create();

        Livewire::actingAs($this->member)->test(StockDetail::class, ['stock' => $stock])
            ->call('delete')->assertRedirect(route('stocks.index'));

        $this->assertModelMissing($stock);
    }

    public function test_admin_can_view_but_not_modify(): void
    {
        $stock = Stock::factory()->for($this->member)->create(['name' => 'Asli']);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(StockDetail::class, ['stock' => $stock])->assertOk();

        foreach ([['startEdit', []], ['save', []], ['adjust', [5]], ['delete', []]] as [$action, $args]) {
            Livewire::actingAs($admin)->test(StockDetail::class, ['stock' => $stock])
                ->call($action, ...$args)->assertForbidden();
        }

        $this->assertSame('Asli', $stock->refresh()->name);
    }
}
