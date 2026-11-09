<?php

namespace App\Features\Stock\Livewire;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Requests\StockRules;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Detail Stok')]
class StockDetail extends Component
{
    use WithFileUploads;

    public Stock $stock;

    public bool $editing = false;

    public string $name = '';

    public string $sku = '';

    public string $description = '';

    public string $quantity = '0';

    public string $unit = 'pcs';

    public string $price = '';

    public string $min_stock = '5';

    public $cover = null;

    public ?string $notice = null;

    public function mount(Stock $stock): void
    {
        Gate::authorize('view', $stock);
        $this->stock = $stock;
    }

    public function startEdit(): void
    {
        Gate::authorize('update', $this->stock);

        $this->name = $this->stock->name;
        $this->sku = (string) $this->stock->sku;
        $this->description = (string) $this->stock->description;
        $this->quantity = (string) $this->stock->quantity;
        $this->unit = $this->stock->unit;
        $this->price = $this->stock->price === null ? '' : (string) $this->stock->price;
        $this->min_stock = (string) $this->stock->min_stock;
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editing', 'cover']);
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('update', $this->stock);
        $data = $this->validate(StockRules::rules());

        app(StockService::class)->update($this->stock, $data, $this->cover);

        $this->stock->refresh();
        $this->reset(['editing', 'cover']);
        $this->notice = 'Stok berhasil diperbarui.';
    }

    public function adjust(int $delta): void
    {
        Gate::authorize('update', $this->stock);
        app(StockService::class)->adjustQuantity($this->stock, $delta);
        $this->stock->refresh();
    }

    public function delete(): void
    {
        Gate::authorize('delete', $this->stock);
        app(StockService::class)->delete($this->stock);

        $this->redirect(route('stocks.index'));
    }

    public function render()
    {
        return view('livewire.stock.stock-detail');
    }
}
