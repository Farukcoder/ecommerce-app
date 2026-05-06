<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix products table schema mismatches:
     *  - status: boolean → enum('draft','published','archived')
     *  - discount_type: 'percent' → 'percentage'
     *  - sku: NOT NULL → nullable
     *  - add thumbnail, meta_title, meta_description columns
     */
    public function up(): void
    {
        // PostgreSQL doesn't support ALTER COLUMN for enums directly,
        // so we use raw SQL to drop & recreate the columns cleanly.

        Schema::table('products', function (Blueprint $table) {
            // Make sku nullable
            $table->string('sku')->nullable()->change();

            // Add new columns if not already present
            if (! Schema::hasColumn('products', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('sku');
            }
            if (! Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });

        // Fix status column: boolean → string-based enum
        // We do it via raw SQL for PostgreSQL compatibility
        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS status');
        DB::statement("ALTER TABLE products ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived'))");

        // Fix discount_type column: drop & recreate with corrected values
        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS discount_type');
        DB::statement("ALTER TABLE products ADD COLUMN discount_type VARCHAR(20) CHECK (discount_type IN ('fixed', 'percentage'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['thumbnail', 'meta_title', 'meta_description']);
            $table->string('sku')->nullable(false)->change();
        });

        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS status');
        DB::statement('ALTER TABLE products ADD COLUMN status BOOLEAN NOT NULL DEFAULT TRUE');

        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS discount_type');
        DB::statement("ALTER TABLE products ADD COLUMN discount_type VARCHAR(20) CHECK (discount_type IN ('fixed', 'percent'))");
    }
};
