<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EventProviderMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'module';

    protected $name = 'module:make-event-provider';

    protected $description = 'Create a new event service provider class for the specified module.';

    public function getDestinationFilePath(): string
    {
        $file_path = GenerateConfigReader::read('provider')->getPath() ?? $this->app_path('Providers');

        return $this->module_app_path($this->getModuleName(), $file_path.'/'.$this->getFileName().'.php');
    }

    protected function getTemplateContents(): string
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassNameWithoutNamespace(),
        ]))->render();
    }

    protected function getArguments(): array
    {
        return [
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
        return Str::studly('EventServiceProvider');
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getFileName());
    }

    public function getDefaultNamespace(): string
    {
        return $this->path_namespace(
            config('modules.paths.generator.provider.namespace') ??
            $this->app_path(config('modules.paths.generator.provider.path', 'app/Providers'))
        );
    }

    protected function getStubName(): string
    {
        return '/event-provider.stub';
    }
}
