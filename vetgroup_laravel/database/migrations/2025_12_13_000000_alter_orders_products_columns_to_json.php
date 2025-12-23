<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize legacy data in products so it can safely become JSON.
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'products')) {
            // Any non-JSON legacy payload becomes NULL to avoid migration failure.
            // New writes are handled via model casts / controller fallback.
            DB::statement("
                UPDATE `orders`
                SET `products` = NULL
                WHERE `products` IS NOT NULL
                  AND JSON_VALID(`products`) = 0
            ");

            Schema::table('orders', function (Blueprint $table): void {
                $table->json('products')->nullable()->change();
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'products_json')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->json('products_json')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'products')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->text('products')->nullable()->change();
            });
        }

        // products_json is already JSON in the original create_orders migration,
        // so we leave it as JSON on rollback as well (no-op).
    }
};

