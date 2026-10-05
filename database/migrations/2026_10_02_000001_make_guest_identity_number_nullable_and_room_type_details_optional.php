<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('identity_number')->nullable()->change();
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('base_price', 12, 2)->nullable()->change();
            $table->integer('capacity')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('base_price', 12, 2)->nullable(false)->change();
            $table->integer('capacity')->nullable(false)->change();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->string('identity_number')->nullable(false)->change();
        });
    }
};
