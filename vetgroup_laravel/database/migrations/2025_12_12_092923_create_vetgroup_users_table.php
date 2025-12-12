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
        if (! Schema::hasTable('vetgroup_users')) {
            Schema::create('vetgroup_users', function (Blueprint $table) {
                $table->id();
                $table->string('username')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('company')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('location')->nullable();
                $table->string('code')->nullable();
                $table->string('user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vetgroup_users');
    }
};
