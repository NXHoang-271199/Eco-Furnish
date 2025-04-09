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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('space_type')->nullable()->after('slug')->comment('Loại không gian: living_room, bedroom, kitchen, dining_room, office, outdoor, other');
            $table->index('space_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['space_type']);
            $table->dropColumn('space_type');
        });
    }
};
