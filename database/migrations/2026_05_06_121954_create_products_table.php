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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->string('thumbnail')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->boolean('featured')->default(false);
            $table->enum('status', ['Draft', 'Published', 'Archived'])->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
