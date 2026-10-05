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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_type')->default('general')->after('booking_status');
            $table->boolean('is_day_use')->default(false)->after('booking_type');
            $table->boolean('is_early_check_out')->default(false)->after('is_day_use');
            $table->boolean('is_bill_merged')->default(false)->after('is_early_check_out');
            $table->decimal('ballroom_amount', 12, 2)->default(0)->after('additional_charge');
            $table->json('additional_charge_breakdown')->nullable()->after('is_bill_merged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_type',
                'is_day_use',
                'is_early_check_out',
                'is_bill_merged',
                'additional_charge_breakdown',
            ]);
        });
    }
};
