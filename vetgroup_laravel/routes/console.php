<?php

use App\Models\Product;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:sync', function () {
    $this->info('Syncing products from external API...');
    Log::info('products:sync started');

    $response = Http::withBasicAuth('001', '001')
        ->acceptJson()
        ->get('http://87.241.165.71:8081/web/hs/Eportal/GET_ITEMS');

    if ($response->failed()) {
        $this->error('Request failed with status '.$response->status());

        return 1;
    }

    $data = $response->json();
    $items = $data['Items'] ?? [];

    $normalizeNumber = function ($value): ?float {
        if ($value === null) {
            return null;
        }

        $string = str_replace(["\u{00A0}", ' '], '', (string) $value);
        $string = str_replace(',', '.', $string);

        return is_numeric($string) ? (float) $string : null;
    };

    foreach ($items as $item) {
        $backendId = $item['ID'] ?? null;

        if (! $backendId) {
            continue;
        }

        $price = isset($item['Price']) ? $normalizeNumber($item['Price']) : null;
        $packPrice = isset($item['pack_price']) ? $normalizeNumber($item['pack_price']) : null;
        $stock = isset($item['Stock']) ? (int) ($normalizeNumber($item['Stock']) ?? 0) : null;

        Product::updateOrCreate(
            ['backend_id' => $backendId],
            [
                'name' => $item['Name'] ?? null,
                'code' => $item['Articul'] ?? null,
                'description' => $item['CatalogName'] ?? null,
                'price' => $price,
                'backend_id' => $backendId,
                'stock' => $stock,
                'pack_price' => $packPrice,
                // 'image' is intentionally not touched here,
                // so it can be managed only from Filament.
            ],
        );
    }

    $count = count($items);

    $this->info('Sync complete. Items processed: '.$count);
    Log::info('products:sync completed', ['items_processed' => $count]);
})->purpose('Sync products from external API');

Schedule::command('products:sync')->hourly();
