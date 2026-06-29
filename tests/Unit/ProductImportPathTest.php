<?php

namespace Tests\Unit;

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImportPathTest extends TestCase
{
    public function test_relative_paths_can_be_resolved_from_base_directory_when_the_file_is_stored_in_the_root(): void
    {
        $baseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'product-import-test';
        File::deleteDirectory($baseDir);
        File::ensureDirectoryExists($baseDir);

        $expectedFile = $baseDir . DIRECTORY_SEPARATOR . 'sample-thumb.jpg';
        file_put_contents($expectedFile, 'test-image');

        $controller = new ProductController();
        $method = new \ReflectionMethod(ProductController::class, 'resolveImportPath');
        $method->setAccessible(true);

        $resolved = $method->invoke($controller, 'images/sample-thumb.jpg', $baseDir);

        $this->assertSame($expectedFile, $resolved);

        File::deleteDirectory($baseDir);
    }
}
