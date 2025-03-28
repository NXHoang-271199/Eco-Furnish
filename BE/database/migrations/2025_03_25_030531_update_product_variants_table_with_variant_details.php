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
        Schema::table('product_variants', function (Blueprint $table) {
            // Thêm cột variant_details dạng JSON
            $table->json('variant_details')->after('product_id')->nullable();
            
            // Bỏ các khóa ngoại nếu có
            if (Schema::hasColumn('product_variants', 'variant_id')) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            }
            
            if (Schema::hasColumn('product_variants', 'variant_value_id')) {
                $table->dropForeign(['variant_value_id']);
                $table->dropColumn('variant_value_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Bỏ cột variant_details
            $table->dropColumn('variant_details');
            
            // Thêm lại các cột cũ
            $table->foreignId('variant_id')->after('product_id')->constrained('variants')->onDelete('cascade');
            $table->foreignId('variant_value_id')->after('variant_id')->constrained('variant_values')->onDelete('cascade');
        });
    }
};
