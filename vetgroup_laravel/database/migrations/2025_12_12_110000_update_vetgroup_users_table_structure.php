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
        Schema::table('vetgroup_users', function (Blueprint $table) {
            if (! Schema::hasColumn('vetgroup_users', 'username')) {
                $table->string('username')->nullable()->after('id');
            }

            if (! Schema::hasColumn('vetgroup_users', 'email')) {
                $table->string('email')->nullable()->after('username');
            }

            if (! Schema::hasColumn('vetgroup_users', 'password')) {
                $table->string('password')->nullable()->after('email');
            }

            if (! Schema::hasColumn('vetgroup_users', 'company')) {
                $table->string('company')->nullable()->after('password');
            }

            if (! Schema::hasColumn('vetgroup_users', 'first_name')) {
                $table->string('first_name')->nullable()->after('company');
            }

            if (! Schema::hasColumn('vetgroup_users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('vetgroup_users', 'location')) {
                $table->string('location')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('vetgroup_users', 'code')) {
                $table->string('code')->nullable()->after('location');
            }

            if (! Schema::hasColumn('vetgroup_users', 'user_id')) {
                $table->string('user_id')->nullable()->after('code');
            }

            // Drop columns you said you don't need, if they exist.
            foreach ([
                'document_id',
                'provider',
                'reset_password_token',
                'confirmation_token',
                'confirmed',
                'blocked',
                'published_at',
                'created_by_id',
                'updated_by_id',
                'locale',
            ] as $column) {
                if (Schema::hasColumn('vetgroup_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vetgroup_users', function (Blueprint $table) {
            // Best-effort revert: drop new columns if present.
            foreach ([
                'username',
                'email',
                'password',
                'company',
                'first_name',
                'last_name',
                'location',
                'code',
                'user_id',
            ] as $column) {
                if (Schema::hasColumn('vetgroup_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

