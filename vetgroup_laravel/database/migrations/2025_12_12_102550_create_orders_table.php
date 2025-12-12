<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('document_id')->nullable();
                $table->string('order_id')->nullable();
                $table->dateTime('created')->nullable();
                $table->float('total')->nullable();
                $table->text('products')->nullable();
                $table->json('products_json')->nullable();
                $table->boolean('complited')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('published_at')->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->unsignedBigInteger('updated_by_id')->nullable();
                $table->string('locale')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
