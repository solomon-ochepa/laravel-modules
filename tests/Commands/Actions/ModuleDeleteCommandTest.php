<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Activators\FileActivator;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ModuleDeleteCommandTest extends BaseTestCase
{
    use MatchesSnapshots;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $finder;

    /**
     * @var FileActivator
     */
    private $activator;

    public function setUp(): void
    {
        parent::setUp();
        $this->finder = $this->app['files'];
        $this->activator = new FileActivator($this->app);
    }

    public function test_it_can_delete_a_module_from_disk(): void
    {
        $this->artisan('module:make', ['name' => ['WrongModule']]);
        $this->assertDirectoryExists(base_path('modules/WrongModule'));

        $code = $this->artisan('module:delete', ['module' => 'WrongModule', '--force' => true]);
        $this->assertFileDoesNotExist(base_path('modules/WrongModule'));
        $this->assertSame(0, $code);
    }

    public function test_it_can_delete_array_module_from_disk(): void
    {
        $modules = [
            'Foo',
            'Bar',
            'Zoo',
        ];

        foreach ($modules as $module) {
            $this->createModule($module);
            $this->assertDirectoryExists($this->getModuleBasePath($module));
        }

        $code = $this->artisan('module:delete', ['module' => ['Foo', 'Bar'], '--force' => true]);
        $this->assertSame(0, $code);
        $this->assertFileDoesNotExist($this->getModuleBasePath('Foo'));
        $this->assertFileDoesNotExist($this->getModuleBasePath('Bar'));
        $this->assertDirectoryExists($this->getModuleBasePath('Zoo'));

        $this->app[RepositoryInterface::class]->delete('Zoo');
    }

    public function test_it_can_delete_all_module_from_disk(): void
    {
        $modules = [
            'Foo',
            'Bar',
            'Zoo',
        ];

        foreach ($modules as $module) {
            $this->createModule($module);
            $this->assertDirectoryExists($this->getModuleBasePath($module));
        }

        $code = $this->artisan('module:delete', ['--all' => true, '--force' => true]);
        $this->assertSame(0, $code);
        $this->assertFileDoesNotExist($this->getModuleBasePath('Foo'));
        $this->assertFileDoesNotExist($this->getModuleBasePath('Bar'));
        $this->assertFileDoesNotExist($this->getModuleBasePath('Zoo'));
    }

    public function test_it_deletes_modules_from_status_file(): void
    {
        $this->artisan('module:make', ['name' => ['WrongModule']]);
        $this->assertMatchesSnapshot($this->finder->get($this->activator->getStatusesFilePath()));

        $code = $this->artisan('module:delete', ['module' => 'WrongModule', '--force' => true]);
        $this->assertMatchesSnapshot($this->finder->get($this->activator->getStatusesFilePath()));
        $this->assertSame(0, $code);
    }
}
