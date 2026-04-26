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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('customer_id')->nullable()->after('channel')->constrained('customers')->nullOnDelete();
            $table->decimal('member_discount', 10, 2)->default(0)->after('discount');
            $table->decimal('birthday_discount', 10, 2)->default(0)->after('member_discount');
            $table->integer('points_used')->default(0)->after('birthday_discount');
            $table->decimal('point_discount', 10, 2)->default(0)->after('points_used');
            $table->integer('points_earned')->default(0)->after('point_discount');

            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'member_discount',
                'birthday_discount',
                'points_used',
                'point_discount',
                'points_earned',
            ]);
        });
    }
};
