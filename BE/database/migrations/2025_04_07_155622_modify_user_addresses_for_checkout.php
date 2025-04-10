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
        Schema::table('user_addresses', function (Blueprint $table) {
            // Đổi tên cột 'name' thành 'full_name' nếu tồn tại
            if (Schema::hasColumn('user_addresses', 'name')) {
                $table->renameColumn('name', 'full_name');
            }
            
            // Thêm các cột mới
            $table->string('first_name', 50)->nullable()->after('user_id');
            $table->string('last_name', 50)->nullable()->after('first_name');
            $table->string('address_name', 100)->nullable()->after('email'); // Tên địa chỉ (Nhà riêng, Công ty,...)
            $table->string('country', 100)->default('Việt Nam')->after('address_name');
            $table->string('province', 100)->nullable()->after('country');
            $table->string('district', 100)->nullable()->after('province');
            $table->string('ward', 100)->nullable()->after('district');
            $table->text('street_address')->nullable()->after('ward'); // Thêm cột địa chỉ đường
            
            // Xóa cột 'address' cũ nếu tồn tại
            if (Schema::hasColumn('user_addresses', 'address')) {
                $table->dropColumn('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            // Đổi tên lại cột 'full_name' thành 'name' nếu tồn tại
            if (Schema::hasColumn('user_addresses', 'full_name')) {
                 $table->renameColumn('full_name', 'name');
            }
           
            // Xóa các cột đã thêm
            $table->dropColumn(['first_name', 'last_name', 'address_name', 'country', 'province', 'district', 'ward', 'street_address']);
            
            // Thêm lại cột 'address' cũ
            $table->text('address')->nullable()->after('email');
        });
    }
};
