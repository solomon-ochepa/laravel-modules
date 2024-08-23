<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TraitMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    protected $name = 'module:make-trait';

    protected $description = 'Create a new trait class for the specified module.';

    public function getDestinationFilePath(): string
    {
        $file_path = GenerateConfigReader::read('traits')->getPath() ?? $this->app_path('Traits');

        return $this->module_app_path($this->getModuleName(), $file_path.'/'.$this->getFileName().'.php');
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
            ['name', InputArgument::REQUIRED, 'The name of the trait class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'su.'],
        ];
    }

    protected function getFileName(): array|string
    {
        return Str::studly($this->argument('name'));
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getFileName());
    }

    public function getDefaultNamespace(): string
    {
        return $this->path_namespace(
            config('modules.paths.generator.traits.namespace') ??
            $this->app_path(config('modules.paths.generator.traits.path', 'app/Traits'))
        );
    }

    protected function getStubName(): string
    {
        return '/trait.stub';
    }
}
