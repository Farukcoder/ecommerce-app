<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('hero_badge_text')->nullable()->after('site_motto');
            $table->string('hero_heading')->nullable()->after('hero_badge_text');
            $table->text('hero_description')->nullable()->after('hero_heading');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['hero_badge_text', 'hero_heading', 'hero_description']);
        });
    }
};
