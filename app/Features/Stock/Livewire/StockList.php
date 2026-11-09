<?php

namespace App\Features\Stock\Livewire;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Requests\StockRules;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('components.layouts.app')]
#[Title('Stok Barang')]
class StockList extends Component
{
    use WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    // Form create/edit
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $sku = '';

    public string $description = '';

    public string $quantity = '0';

    public string $unit = 'pcs';

    public string $price = '';

    public string $min_stock = '5';

    public $cover = null;

    // Import
    public $importFile = null;

    /** @var array<int, string> */
    public array $importErrors = [];

    // AI
    public bool $showAi = false;

    public string $aiPrompt = '';

    public string $aiCount = '5';

    /** @var array<int, array<string, mixed>> */
    public array $aiItems = [];

    /** @var array<int, string> */
    public array $aiSelected = [];

    public ?string $aiError = null;

    public ?string $notice = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    // ---------- CRUD ----------

    public function create(): void
    {
        Gate::authorize('create', Stock::class);
        $this->closeForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $stock = $this->findOwned($id);

        $this->closeForm();
        $this->editingId = $stock->id;
        $this->name = $stock->name;
        $this->sku = (string) $stock->sku;
        $this->description = (string) $stock->description;
        $this->quantity = (string) $stock->quantity;
        $this->unit = $stock->unit;
        $this->price = $stock->price === null ? '' : (string) $stock->price;
        $this->min_stock = (string) $stock->min_stock;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate(StockRules::rules());
        $service = app(StockService::class);

        if ($this->editingId === null) {
            Gate::authorize('create', Stock::class);
            $service->create(Auth::user(), $data, $this->cover);
        } else {
            $service->update($this->findOwned($this->editingId), $data, $this->cover);
        }

        $this->closeForm();
        $this->notice = 'Stok berhasil disimpan.';
    }

    public function closeForm(): void
    {
        $this->reset(['showForm', 'editingId', 'name', 'sku', 'description', 'quantity', 'unit', 'price', 'min_stock', 'cover']);
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $stock = $this->findOwned($id);
        Gate::authorize('delete', $stock);
        app(StockService::class)->delete($stock);
        $this->notice = 'Stok berhasil dihapus.';
    }

    public function adjust(int $id, int $delta): void
    {
        app(StockService::class)->adjustQuantity($this->findOwned($id), $delta);
        $this->notice = null;
    }

    // ---------- Excel ----------

    public function export()
    {
        $content = app(StockExcelService::class)->export(app(StockService::class)->allFor(Auth::user()));

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, 'stok-'.now()->format('Ymd-His').'.xlsx');
    }

    public function downloadTemplate()
    {
        $content = app(StockExcelService::class)->template();

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, 'template-stok.xlsx');
    }

    public function import(): void
    {
        $this->validate(['importFile' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);
        $this->notice = null;

        $result = app(StockExcelService::class)->parse($this->importFile->getRealPath());
        $this->importErrors = $result['errors'];

        if ($result['errors'] === []) {
            $count = app(StockService::class)->createMany(Auth::user(), $result['rows']);
            $this->notice = "{$count} stok berhasil diimpor.";
        }

        $this->reset('importFile');
    }

    // ---------- AI ----------

    public function openAi(): void
    {
        $this->reset(['aiPrompt', 'aiItems', 'aiSelected', 'aiError']);
        $this->showAi = true;
    }

    public function closeAi(): void
    {
        $this->reset(['showAi', 'aiPrompt', 'aiItems', 'aiSelected', 'aiError']);
    }

    public function generateAi(): void
    {
        $this->validate([
            'aiPrompt' => ['required', 'string', 'min:3', 'max:500'],
            'aiCount' => ['required', 'integer', 'between:1,10'],
        ]);

        $this->aiError = null;
        $this->aiItems = [];

        try {
            $items = app(AiClientInterface::class)->generate($this->aiPrompt, (int) $this->aiCount);
        } catch (RuntimeException $e) {
            $this->aiError = $e->getMessage();

            return;
        }

        $this->aiItems = array_map(fn ($item) => $item->toArray(), $items);
        $this->aiSelected = array_map('strval', array_keys($this->aiItems));
    }

    public function saveAiItems(): void
    {
        $selected = array_values(array_filter(
            $this->aiItems,
            fn ($item, $index) => in_array((string) $index, $this->aiSelected, true),
            ARRAY_FILTER_USE_BOTH
        ));

        if ($selected === []) {
            $this->aiError = 'Pilih minimal satu item untuk disimpan.';

            return;
        }

        $count = app(StockService::class)->createMany(Auth::user(), $selected);
        $this->closeAi();
        $this->notice = "{$count} stok dari AI berhasil disimpan.";
    }

    // ---------- util ----------

    private function findOwned(int $id): Stock
    {
        $stock = app(StockService::class)->find($id);
        abort_if($stock === null, 404);
        Gate::authorize('update', $stock);

        return $stock;
    }

    public function render()
    {
        return view('livewire.stock.stock-list', [
            'stocks' => app(StockService::class)->paginateFor(Auth::user(), $this->search, $this->statusFilter),
            'statuses' => StockStatus::cases(),
        ]);
    }
}
