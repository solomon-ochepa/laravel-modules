<?php

namespace Nwidart\Modules\Traits;

use Illuminate\Support\Str;

trait PathNamespace
{
    /**
     * Get well-formatted StudlyCase path(s).
     */
    public function studly_path(string ...$path): string
    {
        $ds = DIRECTORY_SEPARATOR;

        return collect(explode($ds, implode($ds, $path)))
            ->map(fn ($path) => Str::studly($path))
            ->implode($ds);
    }

    /**
     * Get well-formatted StudlyCase namespace(s).
     */
    public function studly_namespace(string ...$namespace): string
    {
        $ds = '\\';

        // dd(Str::of(implode($ds, $namespace))->explode($ds));
        return Str::of(implode($ds, $namespace))->explode($ds);
        collect(explode($ds, implode($ds, $namespace)))
            ->map(fn ($namespace) => Str::studly($namespace))
            ->implode($ds);

        return collect(explode($ds, implode($ds, $namespace)))
            ->map(fn ($namespace) => Str::studly($namespace))
            ->implode($ds);
    }

    /**
     * Get a well-formatted namespace from a given path or paths.
     */
    public function path_namespace(string ...$path): string
    {
        $ds = '\\';

        return Str::of($this->studly_path(implode($ds, $path)))
            ->replace('/', $ds)
            ->trim($ds);
    }

    /**
     * Get a well-formatted StudlyCase namespace for a module, with an optional additional path.
     */
    public function module_namespace(string $name, ?string ...$path): string
    {
        $ds = '\\';
        $module_namespace = config('modules.namespace', $this->path_namespace(config('modules.paths.modules'))) . $ds . ($name);
        $module_namespace .= count($path) ? $ds . $this->path_namespace(implode($ds, $path)) : '';

        return $this->studly_namespace($module_namespace);
    }

    /**
     * Clean path
     */
    public function clean_namespace(string ...$path): string
    {
        $ds = '\\';

        return Str::of(implode($ds, $path))
            ->explode($ds)
            ->reject(empty($path))
            ->implode($ds);
    }

    /**
     * Clean path
     */
    public function clean_path(string ...$path): string
    {
        $ds = '/';

        return Str::of(implode($ds, $path))
            ->replace('\\', $ds)
            ->explode($ds)
            ->reject(empty($path))
            ->implode($ds);
    }

    /**
     * Get the app path basename.
     */
    public function app_path(?string $path = null): string
    {
        $config_path = config('modules.paths.app_folder');

        // Get modules config app path or use Laravel default app path.
        $app_path = strlen($config_path) ? $config_path : 'app/';

        if ($path) {
            // Replace duplicate custom|default app paths
            $replaces = array_unique([$this->clean_path($app_path).'/', 'app/']);
            do {
                $path = Str::of($path)->replaceStart($app_path, '')->replaceStart('app/', '');
            } while (Str::of($path)->startsWith($replaces));

            // Append additional path
            $app_path .= strlen($path) ? '/'.$path : '';
        }

        return $this->clean_path($app_path);
    }
}
