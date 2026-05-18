<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('header_settings', function (Blueprint $table) {
            $table->id();
            $table->string('header_logo')->nullable();
            $table->boolean('show_language_switcher')->default(true);
            $table->boolean('show_currency_switcher')->default(true);
            $table->boolean('enable_sticky_header')->default(true);
            $table->string('top_header_bg_color')->default('#FFFFFF');
            $table->string('bottom_header_bg_color')->default('#FFFFFF');
            $table->string('top_header_text_color')->default('#000000');
            $table->string('bottom_header_text_color')->default('#000000');
            $table->string('help_line_number')->nullable();
            $table->json('header_nav_menu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('header_settings');
    }
};
