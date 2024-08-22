<?php

namespace Nwidart\Modules\Commands\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class FactoryMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-factory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model factory for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/factory.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'NAME' => $this->getModelName(),
            'MODEL_NAMESPACE' => $this->getModelNamespace(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $file_path = GenerateConfigReader::read('factory')->getPath() ?? 'database/factories';

        return $this->module_path($this->getModuleName(), $file_path.'/'.$this->getFileName());
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name')).'Factory.php';
    }

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('name'));
    }

    /**
     * Get default namespace.
     */
    public function getDefaultNamespace(): string
    {
        return $this->path_namespace(
            config('modules.paths.generator.factory.namespace') ??
            $this->path_namespace(config('modules.paths.generator.factory.path', 'database/factories'))
        );
    }

    /**
     * Get model namespace.
     */
    public function getModelNamespace(): string
    {
        return $this->module_namespace($this->getModuleName(), config('modules.paths.generator.model.path', 'app/Models'));
    }
}
