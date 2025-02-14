<?php

namespace Nwidart\Modules;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Nwidart\Modules\Contracts\ActivatorInterface;

class ModuleManifest
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    private $files;

    /**
     * The base path.
     *
     * @var string
     */
    public $paths;

    /**
     * The manifest path.
     *
     * @var string|null
     */
    public $manifestPath;

    /**
     * The manifestData
     */
    private static ?Collection $manifestData;

    /**
     * The loaded manifest array.
     *
     * @var array
     */
    private $manifest;

    /**
     * module activator class
     */
    private ActivatorInterface $activator;

    /**
     * Create a new package manifest instance.
     *
     * @param  Collection  $paths
     * @param  string  $manifestPath
     * @return void
     */
    public function __construct(Filesystem $files, $paths, $manifestPath, ActivatorInterface $activator)
    {
        $this->files = $files;
        $this->paths = collect($paths);
        $this->manifestPath = $manifestPath;
        $this->activator = $activator;
    }

    /**
     * Get the current package manifest.
     */
    public function getProviders(): array
    {
        if (! is_null($this->manifest)) {
            return $this->manifest;
        }

        return $this->manifest = $this->build();
    }

    /**
     * Build the manifest and write it to disk.
     */
    public function build(): array
    {
        return $this->getModulesData()
            ->pluck('providers')
            ->flatten()
            ->filter()
            ->toArray();
    }

    public function registerFiles(): void
    {
        // todo check this section store on module.php or not?
        $this->getModulesData()
            ->each(function (array $manifest) {
                if (empty($manifest['files'])) {
                    return;
                }

                foreach ($manifest['files'] as $file) {
                    include_once $manifest['module_directory'].DIRECTORY_SEPARATOR.$file;
                }
            });
    }

    public function getModulesData(): Collection
    {
        if (! empty(self::$manifestData) && ! app()->runningUnitTests()) {
            return self::$manifestData;
        }

        self::$manifestData = $this->paths
            ->flatMap(function ($path) {
                $manifests = $this->files->glob("{$path}/module.json");
                is_array($manifests) || $manifests = [];

                return collect($manifests)
                    ->map(function ($manifest) {
                        return [
                            'module_directory' => dirname($manifest),
                            ...$this->files->json($manifest),
                        ];
                    });
            })
            ->filter(fn ($module) => $this->activator->hasStatus($module['name'], true))
            ->sortBy(fn ($module) => $module['priority'] ?? 0);

        return self::$manifestData;
    }
}
