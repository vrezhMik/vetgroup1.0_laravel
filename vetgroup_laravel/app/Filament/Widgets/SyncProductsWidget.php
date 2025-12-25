<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;

class SyncProductsWidget extends Widget
{
    protected string $view = 'filament.widgets.sync-products-widget';

    protected ?string $heading = 'Products Sync';

    public function syncProducts(): void
    {
        Artisan::call('products:sync');
    }
}
