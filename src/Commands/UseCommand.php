<?php

namespace Nwidart\Modules\Commands;

class UseCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:use';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use the specified module.';

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $this->components->task("Using {$module->getName()} module", function () use ($module) {
            $this->laravel['modules']->setUsed($module);
        });
    }

    function getInfo(): string|null
    {
        return 'Using Module ...';
    }
}
