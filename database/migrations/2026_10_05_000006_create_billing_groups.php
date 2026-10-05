<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_groups', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('payer_guest_id')->constrained('guests')->restrictOnDelete();
            $table->string('payment_status')->default('unpaid');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('billing_group_id')->nullable()->after('guest_id')
                ->constrained('billing_groups')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('billing_group_id')->nullable()->after('booking_id')
                ->constrained('billing_groups')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->change();
        });

        Schema::create('booking_payment_allocations', function (Blueprint $table) {
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->primary(['payment_id', 'booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payment_allocations');
        DB::table('payments')->whereNotNull('billing_group_id')->delete();

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_group_id');
            $table->foreignId('booking_id')->nullable(false)->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_group_id');
        });

        Schema::dropIfExists('billing_groups');
    }
};
