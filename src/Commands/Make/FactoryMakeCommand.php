<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FactoryMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     */
    protected $name = 'module:make-factory';

    /**
     * The console command description.
     */
    protected $description = 'Create a new model factory for the specified module.';

    protected function name(): string
    {
        return Str::of($this->argument('name'))->chopEnd('Factory')->studly();
    }

    protected function class(): string
    {
        return Str::of($this->name())->append('Factory');
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Model name [if different from factory name]', null],
        ];
    }

    protected function getTemplateContents(): mixed
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        dd($this->getDefaultNamespace(), $this->getClassNamespace($module), $this->modelNamespace());

        return (new Stub('/factory.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->class(),
            'MODEL' => $this->model(),
            'MODEL_NAMESPACE' => $this->modelNamespace(),
        ]))->render();
    }

    protected function getDestinationFilePath(): mixed
    {
        $path = GenerateConfigReader::read('factory')->getPath();

        return module_path($this->getModuleName(), $path . '/' . $this->filename());
    }

    private function filename(): string
    {
        return Str::of($this->class())->append('.php');
    }

    private function model(): mixed
    {
        return $this->option('model') ?? $this->name();
    }

    /**
     * Get default namespace.
     */
    public function getDefaultNamespace(): string
    {
        return dd($this->studly_namespace(config('modules.paths.generator.factory.namespace', config('modules.paths.generator.factory.path', 'database/factories'))));
    }

    /**
     * Get the model namespace.
     */
    public function modelNamespace(): string
    {
        $path = ltrim($this->app_path(config('modules.paths.generator.model.path', 'app/Models')), $this->app_path());
        $path = $this->clean_path($path);

        return $this->module_namespace($this->laravel['modules']->findOrFail($this->getModuleName()), $path);
    }
}
