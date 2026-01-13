<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use UnitEnum;

class MediaPage extends Page
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    protected array $queryString = [
        'perPage' => ['except' => 10],
    ];

    public int $perPage = 10;

    public array $perPageOptions = [10, 35, 50, 100];

    protected string $view = 'filament.pages.media';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationLabel = 'Media';

    protected static UnitEnum|string|null $navigationGroup = 'Vetgroup';

    public array $selectedImages = [];

    protected function getViewData(): array
    {
        $images = $this->resolveImageCollection();

        $total = $images->count();
        $lastPage = (int) max(1, ceil($total / $this->perPage));
        $currentPage = (int) max(1, min($this->getPage(), $lastPage));

        $paginator = new LengthAwarePaginator(
            $images->forPage($currentPage, $this->perPage),
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
            ]
        );

        $this->setPage($currentPage);

        return [
            'images' => $paginator,
        ];
    }

    public function deleteSelected(): void
    {
        $disk = Storage::disk('uploads');
        $paths = array_values(array_filter(array_unique($this->selectedImages)));

        if (count($paths) === 0) {
            Notification::make()
                ->danger()
                ->title('No images selected')
                ->body('Select at least one media item to delete.')
                ->send();

            return;
        }

        $deleted = 0;

        foreach ($paths as $path) {
            if ($disk->exists($path) && $disk->delete($path)) {
                $deleted++;

                $queryPath = $path;
                $queryUploads = ltrim("uploads/{$path}", '/');
                $queryVariants = [
                    $queryPath,
                    $queryUploads,
                    "/{$queryPath}",
                    "/{$queryUploads}",
                ];

                Product::whereIn('image', $queryVariants)
                    ->update(['image' => null]);
            }
        }

        $this->selectedImages = [];

        if ($deleted === 0) {
            Notification::make()
                ->danger()
                ->title('Nothing removed')
                ->body('The selected media could not be found.')
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Removed media')
            ->body("Deleted {$deleted} item(s).")
            ->send();

        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = (int) $perPage;
        $this->resetPage();
    }

    protected function resolveImageCollection(): Collection
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('uploads');

        return collect($disk->allFiles())
            ->filter(fn (string $path): bool => (bool) preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $path))
            ->map(function (string $path) use ($disk): array {
                $size = $disk->size($path);

                return [
                    'name' => basename($path),
                    'path' => $path,
                    'url' => $disk->url($path),
                    'size' => $size,
                    'formatted_size' => $this->formatBytes($size),
                ];
            })
            ->values();
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min(count($units) - 1, (int) floor(log($bytes, 1024)));
        $precision = $power === 0 ? 0 : 1;

        return sprintf('%s %s', number_format($bytes / (1024 ** $power), $precision), $units[$power]);
    }
}
