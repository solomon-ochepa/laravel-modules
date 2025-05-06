<?php

namespace Nwidart\Modules\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Nwidart\Modules\Constants\ModuleEvent;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\PathNamespace;

class ModuleGenerator extends Generator
{
    use PathNamespace;

    /**
     * The module name will created.
     */
    protected ?string $name = null;

    /**
     * The laravel config instance.
     */
    protected ?Config $config = null;

    /**
     * The laravel filesystem instance.
     */
    protected ?Filesystem $filesystem = null;

    /**
     * The laravel console instance.
     */
    protected ?Console $console = null;

    /**
     * The laravel component Factory instance.
     */
    protected ?Factory $component = null;

    /**
     * The activator instance
     */
    protected ?ActivatorInterface $activator = null;

    /**
     * The module instance.
     */
    protected mixed $module = null;

    /**
     * Force status.
     */
    protected bool $force = false;

    /**
     * set default module type.
     */
    protected string $type = 'web';

    /**
     * Enables the module.
     */
    protected bool $isActive = false;

    /**
     * Module author
     */
    protected array $author = [
        'name',
        'email',
    ];

    /**
     * Vendor name
     */
    protected ?string $vendor = null;

    /**
     * The constructor.
     */
    public function __construct(
        $name,
        ?FileRepository $module = null,
        ?Config $config = null,
        ?Filesystem $filesystem = null,
        ?Console $console = null,
        ?ActivatorInterface $activator = null
    ) {
        $this->name = $name;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;
        $this->activator = $activator;
    }

    /**
     * Set type.
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set active flag.
     */
    public function setActive(bool $active): self
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * Get the name of module that will be created (in StudlyCase).
     *
     * @deprecated use `name()` instead.
     */
    public function getName(): string
    {
        return $this->name();
    }

    /**
     * Get the name of the module.
     */
    public function name(): string
    {
        return Str::studly($this->name);
    }

    /**
     * Get the laravel config instance.
     *
     * @deprecated use `config()` instead.
     */
    public function getConfig(): Config
    {
        return $this->config();
    }

    /**
     * Get the laravel config instance.
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * Set the laravel config instance.
     */
    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the modules activator
     */
    public function setActivator(ActivatorInterface $activator): self
    {
        $this->activator = $activator;

        return $this;
    }

    /**
     * Get the laravel filesystem instance.
     *
     * @deprecated use `filesystem()` instead.
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem();
    }

    /**
     * Get the laravel filesystem instance.
     */
    public function filesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set the laravel filesystem instance.
     */
    public function setFilesystem(Filesystem $filesystem): self
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get the laravel console instance.
     *
     * @deprecated use `console()` instead.
     */
    public function getConsole(): Console
    {
        return $this->console();
    }

    /**
     * Get the laravel console instance.
     */
    public function console(): Console
    {
        return $this->console;
    }

    /**
     * Set the laravel console instance.
     */
    public function setConsole(Console $console): self
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Get the laravel component instance.
     *
     * @deprecated use `component()` instead.
     */
    public function getComponent(): Factory
    {
        return $this->component();
    }

    /**
     * Get the laravel component instance.
     */
    public function component(): Factory
    {
        return $this->component;
    }

    public function setComponent(\Illuminate\Console\View\Components\Factory $component): self
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get the module instance.
     */
    public function getModule(): Module
    {
        return $this->get();
    }

    /**
     * Get the module instance.
     */
    public function get(): Module
    {
        return $this->module;
    }

    /**
     * Set the module instance.
     */
    public function setModule(mixed $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Setting the author from the command
     */
    public function setAuthor(?string $name = null, ?string $email = null): self
    {
        $this->author['name'] = $name;
        $this->author['email'] = $email;

        return $this;
    }

    /**
     * Installing vendor from the command
     */
    public function setVendor(?string $vendor = null): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get the list of folders will created.
     *
     * @deprecated use `paths()` instead.
     */
    public function getFolders(): array
    {
        return $this->paths();
    }

    /**
     * Get the list of paths that will be created.
     */
    public function paths(): array
    {
        return $this->module->config('paths.generator');
    }

    /**
     * Get the list of files will created.
     *
     * @deprecated use `files()` instead.
     */
    public function getFiles(): array
    {
        return $this->files();
    }

    /**
     * Get the list of files will created.
     */
    public function files(): array
    {
        return $this->module->config('stubs.files');
    }

    /**
     * Set force status.
     */
    public function setForce(bool|int $force): self
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Generate the module.
     */
    public function generate(): int
    {
        $name = $this->name();

        if ($this->module->has($name)) {
            if ($this->force) {
                $this->module->delete($name);
            } else {
                $this->component->error("Module [{$name}] already exists!");

                return E_ERROR;
            }
        }

        Event::dispatch(sprintf('modules.%s.%s', strtolower($name), ModuleEvent::CREATING));

        $this->component->info("Creating module: [$name]");

        $this->generateFolders();

        $this->generateModuleJsonFile();

        if ($this->type !== 'plain') {
            $this->generateFiles();
            $this->module->resetModules();
            $this->generateResources();
        }

        if ($this->type === 'plain') {
            $this->cleanModuleJsonFile();
            $this->module->resetModules();
        }

        $this->activator->setActiveByName($name, $this->isActive);

        $this->console->newLine(1);

        $this->component->info("Module [{$name}] created successfully.");

        $this->fireEvent(ModuleEvent::CREATED);

        return 0;
    }

    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach (array_keys($this->paths()) as $key) {
            $folder = GenerateConfigReader::read($key);

            if ($folder->generate() === false) {
                continue;
            }

            $path = $this->module->getModulePath($this->name()).'/'.$folder->path();

            $this->filesystem->ensureDirectoryExists($path, 0755, true);
            if (config('modules.stubs.gitkeep')) {
                $this->generateGitKeep($path);
            }
        }
    }

    /**
     * Generate git keep to the specified path.
     */
    public function generateGitKeep(string $path)
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }

    /**
     * Generate the files.
     */
    public function generateFiles()
    {
        foreach ($this->files() as $stub => $file) {
            $path = $this->module->getModulePath($this->name()).$file;

            $this->component->task("Generating file {$path}", function () use ($stub, $path) {
                if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                    $this->filesystem->makeDirectory($dir, 0775, true);
                }

                $this->filesystem->put($path, $this->getStubContents($stub));
            });
        }
    }

    /**
     * Generate some resources.
     */
    public function generateResources()
    {
        if (GenerateConfigReader::read('seeder')->generate() === true) {
            $this->console->call('module:make-seed', [
                'name' => $this->name(),
                'module' => $this->name(),
                '--master' => true,
            ]);
        }

        $providerGenerator = GenerateConfigReader::read('provider');
        if ($providerGenerator->generate() === true) {
            $this->console->call('module:make-provider', [
                'name' => $this->name().'ServiceProvider',
                'module' => $this->name(),
                '--master' => true,
            ]);
        } else {
            // delete register ServiceProvider on module.json
            $path = $this->module->getModulePath($this->name()).DIRECTORY_SEPARATOR.'module.json';
            $module_file = $this->filesystem->get($path);
            $this->filesystem->put(
                $path,
                preg_replace('/"providers": \[.*?\],/s', '"providers": [ ],', $module_file)
            );
        }

        $eventGeneratorConfig = GenerateConfigReader::read('event-provider');
        if (
            (is_null($eventGeneratorConfig->getPath()) && $providerGenerator->generate())
            || (! is_null($eventGeneratorConfig->getPath()) && $eventGeneratorConfig->generate())
        ) {
            $this->console->call('module:make-event-provider', [
                'module' => $this->name(),
            ]);
        } else {
            if ($providerGenerator->generate()) {
                // comment register EventServiceProvider
                $this->filesystem->replaceInFile(
                    '$this->app->register(Event',
                    '// $this->app->register(Event',
                    $this->module->getModulePath($this->name()).DIRECTORY_SEPARATOR.$providerGenerator->path(sprintf('%sServiceProvider.php', $this->name()))
                );
            }
        }

        $routeGeneratorConfig = GenerateConfigReader::read('route-provider');
        if (
            (is_null($routeGeneratorConfig->getPath()) && $providerGenerator->generate())
            || (! is_null($routeGeneratorConfig->getPath()) && $routeGeneratorConfig->generate())
        ) {
            $this->console->call('module:route-provider', [
                'module' => $this->name(),
            ]);
        } else {
            if ($providerGenerator->generate()) {
                // comment register RouteServiceProvider
                $this->filesystem->replaceInFile(
                    '$this->app->register(Route',
                    '// $this->app->register(Route',
                    $this->module->getModulePath($this->name()).DIRECTORY_SEPARATOR.$providerGenerator->path(sprintf('%sServiceProvider.php', $this->name()))
                );
            }
        }

        if (GenerateConfigReader::read('controller')->generate() === true) {
            $options = $this->type == 'api' ? ['--api' => true] : [];
            $this->console->call('module:make-controller', [
                'controller' => $this->name().'Controller',
                'module' => $this->name(),
            ] + $options);
        }
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @deprecated use `stub()` instead.
     */
    protected function getStubContents($stub): string
    {
        return $this->stub($stub);
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     */
    protected function stub($stub): string
    {
        return (new Stub('/'.$stub.'.stub', $this->replacement($stub)))->render();
    }

    /**
     * get the list for the replacements.
     *
     * @deprecated use `replacements()` instead.
     */
    public function getReplacements()
    {
        return $this->replacements();
    }

    /**
     * get the list for the replacements.
     */
    public function replacements()
    {
        return $this->module->config('stubs.replacements');
    }

    /**
     * Get array replacement for the specified stub.
     *
     * @deprecated use `replacement()` instead.
     */
    protected function getReplacement($stub): array
    {
        return $this->replacement($stub);
    }

    /**
     * Get array replacement for the specified stub.
     */
    protected function replacement($stub): array
    {
        $replacements = $this->module->config('stubs.replacements');

        // Temporarily check if the replacements are defined; remove in the next major version.
        if (! isset($replacements['composer']['APP_FOLDER_NAME'])) {
            $replacements['composer'][] = 'APP_FOLDER_NAME';
        }
        if (! isset($replacements['routes/web']['PLURAL_LOWER_NAME'])) {
            $replacements['routes/web'][] = 'PLURAL_LOWER_NAME';
        }
        if (! isset($replacements['routes/api']['PLURAL_LOWER_NAME'])) {
            $replacements['routes/api'][] = 'PLURAL_LOWER_NAME';
        }

        if (! isset($replacements[$stub])) {
            return [];
        }

        $keys = $replacements[$stub];

        $replaces = [];

        if ($stub === 'json' || $stub === 'composer') {
            if (in_array('PROVIDER_NAMESPACE', $keys, true) === false) {
                $keys[] = 'PROVIDER_NAMESPACE';
            }
        }

        foreach ($keys as $key => $value) {
            if ($value instanceof \Closure) {
                $replaces[strtoupper($key)] = $value($this);
            } elseif (method_exists($this, $method = 'get'.ucfirst(Str::studly(strtolower($value))).'Replacement')) {
                $replace = $this->$method();

                if ($stub === 'routes/web' || $stub === 'routes/api') {
                    $replace = str_replace('\\\\', '\\', $replace);
                }

                $replaces[$value] = $replace;
            } else {
                $replaces[$value] = null;
            }
        }

        return $replaces;
    }

    /**
     * Generate the module.json file
     */
    private function generateModuleJsonFile()
    {
        $path = $this->module->getModulePath($this->name()).'module.json';

        $this->component->task("Generating file $path", function () use ($path) {
            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->stub('json'));
        });
    }

    /**
     * Remove the default service provider that was added in the module.json file
     * This is needed when a --plain module was created
     */
    private function cleanModuleJsonFile()
    {
        $json = $this->module->getModulePath($this->name()).'module.json';

        $content = $this->filesystem->get($path);
        $namespace = $this->getModuleNamespaceReplacement();
        $studlyName = $this->getStudlyNameReplacement();

        $provider = Str::of($this->module_namespace($this->name(), $namespace))->replace('\\', '\\\\');
        $content = str_replace('"'.$provider.'"', '', $content);

        $content = str_replace($provider, '', $content);

        $this->filesystem->put($path, $content);
    }

    /**
     * Get the module name in lower case.
     */
    protected function getLowerNameReplacement(): string
    {
        return strtolower($this->name());
    }

    /**
     * Get the module name in lowercase plural form.
     */
    protected function getPluralLowerNameReplacement(): string
    {
        return Str::of($this->name())->lower()->plural();
    }

    protected function getKebabNameReplacement(): string
    {
        return Str::kebab($this->name());
    }

    /**
     * Get the module name in studly case.
     */
    protected function getStudlyNameReplacement(): string
    {
        return $this->name();
    }

    /**
     * Get the module name in plural studly case.
     */
    protected function getPluralStudlyNameReplacement(): string
    {
        return Str::of($this->name())->pluralStudly();
    }

    /**
     * Get replacement for $VENDOR$.
     */
    protected function getVendorReplacement(): string
    {
        return $this->vendor ?: $this->module->config('composer.vendor');
    }

    /**
     * Get replacement for $MODULE_NAMESPACE$.
     */
    protected function getModuleNamespaceReplacement(): string
    {
        return str_replace('\\', '\\\\', $this->module->config('namespace') ?? $this->path_namespace($this->module->config('paths.modules')));
    }

    /**
     * Get replacement for $CONTROLLER_NAMESPACE$.
     */
    private function getControllerNamespaceReplacement(): string
    {
        if ($this->module->config('paths.generator.controller.namespace')) {
            return $this->module->config('paths.generator.controller.namespace');
        } else {
            return $this->path_namespace(ltrim($this->module->config('paths.generator.controller.path', 'app/Http/Controllers'), config('modules.paths.app_folder')));
        }
    }

    /**
     * Get replacement for $AUTHOR_NAME$.
     */
    protected function getAuthorNameReplacement(): string
    {
        return $this->author['name'] ?: $this->module->config('composer.author.name');
    }

    /**
     * Get replacement for $AUTHOR_EMAIL$.
     */
    protected function getAuthorEmailReplacement(): string
    {
        return $this->author['email'] ?: $this->module->config('composer.author.email');
    }

    /**
     * Get replacement for $APP_FOLDER_NAME$.
     */
    protected function getAppFolderNameReplacement(): string
    {
        return $this->module->config('paths.app_folder');
    }

    protected function getProviderNamespaceReplacement(): string
    {
        return str_replace('\\', '\\\\', GenerateConfigReader::read('provider')->namespace());
    }

    /**
     * fire the module event.
     *
     * @deprecated use `event()` instead.
     */
    protected function fireEvent(string $event): void
    {
        $this->event($event);
    }

    /**
     * Fire the module event.
     */
    protected function event(string $event): void
    {
        $module = $this->module->find($this->name);

        $module->fireEvent($event);
    }
}
