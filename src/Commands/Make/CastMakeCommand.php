<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CastMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    protected $name = 'module:make-cast';

    protected $description = 'Create a new Eloquent cast class for the specified module.';

    public function getDestinationFilePath(): string
    {
        $file_path = GenerateConfigReader::read('casts')->getPath() ?? $this->app_path('Casts');

        return $this->module_app_path($this->getModuleName(), $file_path.'/'.$this->getCastName().'.php');
    }

    protected function getTemplateContents(): string
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassNameWithoutNamespace(),
        ]))->render();
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the cast class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'su.'],
        ];
    }

    protected function getCastName(): array|string
    {
        return Str::studly($this->argument('name'));
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getCastName());
    }

    public function getDefaultNamespace(): string
    {
        return $this->path_namespace(
            config('modules.paths.generator.casts.namespace') ??
            $this->app_path(config('modules.paths.generator.casts.path', 'app/Casts'))
        );
    }

    protected function getStubName(): string
    {
        return '/cast.stub';
    }
}
