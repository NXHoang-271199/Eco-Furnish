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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên quyền (ví dụ: view-dashboard, create-post)
            $table->string('slug')->unique(); // Slug của quyền (ví dụ: view-dashboard, create-post)
            $table->string('description')->nullable(); // Mô tả quyền
            $table->string('model')->nullable(); // Model liên quan (ví dụ: App\Models\Post)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
}; 