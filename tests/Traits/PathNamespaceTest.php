<?php

namespace Nwidart\Modules\Tests\Traits;

use Nwidart\Modules\Tests\BaseTestCase;

class PathNamespaceTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_studly_path()
    {
        $this->assertSame('Modules/Blog/App/Services', $this->studly_path('modules/Blog/app/Services'));
    }

    public function test_studly_namespace()
    {
        $this->assertSame('Modules\Blog\App\Services', $this->studly_namespace('modules/Blog/app/Services'));
    }

    public function test_path_namespace()
    {
        $this->assertSame('Modules\Blog\App\Services', $this->path_namespace('modules/Blog/app/Services'));
    }

    public function test_module_namespace()
    {
        $this->assertSame('Modules\Blog\Database\Seeder', $this->module_namespace('Blog', 'database/seeder'));
    }

    public function test_clean_path()
    {
        $this->assertSame('modules/Blog/app/Services', $this->clean_path('/modules//Blog\app\Services//'));
        $this->assertSame('/', $this->clean_path(''));
    }

    public function test_app_path()
    {
        $config_app_path = config('modules.paths.app', 'app/');

        $this->assertSame($config_app_path, $this->app_path());
        $this->assertSame($config_app_path, $this->app_path(null));
        $this->assertSame('app/Services/BlogService', $this->app_path('Services/BlogService'));
    }
}
