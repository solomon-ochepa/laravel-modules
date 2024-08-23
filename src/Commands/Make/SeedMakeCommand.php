<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\CanClearModulesCache;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SeedMakeCommand extends GeneratorCommand
{
    use CanClearModulesCache;
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    /**
     * The console command name.
     */
    protected $name = 'module:make-seed';

    /**
     * The console command description.
     */
    protected $description = 'Create a new seeder for the specified module.';

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of seeder will be created.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            [
                'master',
                null,
                InputOption::VALUE_NONE,
                'Indicates the seeder will created is a master database seeder.',
            ],
        ];
    }

    protected function getTemplateContents(): mixed
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/seeder.stub', [
            'NAME' => $this->getFileName(),
            'MODULE' => $this->getModuleName(),
            'NAMESPACE' => $this->getClassNamespace($module),

        ]))->render();
    }

    protected function getDestinationFilePath(): mixed
    {
        $file_path = GenerateConfigReader::read('seeder')->getPath() ?? 'database/seeders';

        return $this->module_path($this->getModuleName(), $file_path.'/'.$this->getFileName().'.php');
    }

    /**
     * Get the seeder name.
     */
    private function getFileName(): string
    {
        $string = $this->argument('name');
        $string .= $this->option('master') ? 'Database' : '';
        $suffix = 'Seeder';

        if (strpos($string, $suffix) === false) {
            $string .= $suffix;
        }

        return Str::studly($string);
    }

    /**
     * Get default namespace.
     */
    public function getDefaultNamespace(): string
    {
        return $this->path_namespace(
            config('modules.paths.generator.seeder.namespace') ??
            $this->clean_path(config('modules.paths.generator.seeder.path', 'database/seeders'))
        );
    }
}
