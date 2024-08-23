<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class HelperMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    protected $name = 'module:make-helper';

    protected $description = 'Create a new helper class for the specified module.';

    public function getDestinationFilePath(): string
    {
        $file_path = GenerateConfigReader::read('helpers')->getPath() ?? $this->app_path('Helpers');

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
            ['name', InputArgument::REQUIRED, 'The name of the helper class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate an invokable class', null],
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
            config('modules.paths.generator.helpers.namespace') ??
            $this->app_path(config('modules.paths.generator.helpers.path', 'app/Helpers'))
        );
    }

    protected function getStubName(): string
    {
        return $this->option('invokable') === true ? '/helper-invoke.stub' : '/helper.stub';
    }
}
