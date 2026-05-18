<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('system_name');
            $table->string('frontend_website_name');
            $table->string('site_motto')->nullable();
            $table->string('site_icon')->nullable();
            $table->string('system_logo_white')->nullable();
            $table->string('system_logo_black')->nullable();
            $table->string('uploaded_image_format')->default('webp');
            $table->string('website_base_color')->default('#0080FF');
            $table->string('website_base_hover_color')->default('#0066CC');
            $table->string('website_secondary_base_color')->default('#171717');
            $table->string('website_secondary_base_hover_color')->default('#5D5D62');
            $table->string('flash_deal_page_banner_large')->nullable();
            $table->string('flash_deal_page_banner_small')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
