<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('BDT')->after('product_default_image');
            $table->string('currency_symbol', 10)->default('৳')->after('currency_code');
            $table->string('currency_symbol_position', 10)->default('before')->after('currency_symbol');
            $table->unsignedTinyInteger('currency_decimal_places')->default(2)->after('currency_symbol_position');
            $table->string('currency_thousands_separator', 5)->default(',')->after('currency_decimal_places');
            $table->string('currency_decimal_separator', 5)->default('.')->after('currency_thousands_separator');
            $table->json('available_currencies')->nullable()->after('currency_decimal_separator');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'currency_symbol',
                'currency_symbol_position',
                'currency_decimal_places',
                'currency_thousands_separator',
                'currency_decimal_separator',
                'available_currencies',
            ]);
        });
    }
};
