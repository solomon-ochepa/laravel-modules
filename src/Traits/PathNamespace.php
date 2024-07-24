<?php

namespace Nwidart\Modules\Traits;

use Illuminate\Support\Str;

trait PathNamespace
{
    /**
     * Format a string of path/namespace
     */
    public function clean(string $string, $ds = '/'): string
    {
        return Str::of($string)
            ->replace(($ds == '/') ? '\\' : '/', $ds)
            ->explode($ds)
            ->reject(fn ($str) => empty($str))
            ->implode($ds);
    }

    /**
     * Clean namespace
     */
    public function clean_namespace(string $namespace, $ds = '\\'): string
    {
        return $this->clean($namespace, $ds);
    }

    /**
     * Clean path
     */
    public function clean_path(string $path, $ds = '/'): string
    {
        return $this->clean($path, $ds).(Str::contains($path, '.') ? '' : $ds);
    }

    /**
     * Get a well-formatted StudlyCase string.
     */
    public function studly(string $string, $ds = '/'): string
    {
        return Str::of($string)->explode($ds)->reject(fn ($p) => empty($p))->map(fn ($p) => Str::studly($p))->implode($ds);
    }

    /**
     * Get a well-formatted StudlyCase namespace.
     */
    public function studly_namespace(string $namespace, $ds = '\\'): string
    {
        return $this->studly($this->clean_namespace($namespace, $ds), $ds);
    }

    /**
     * Get a well-formatted StudlyCase representation of path components.
     */
    public function studly_path(string $path, $ds = '/'): string
    {
        return $this->clean_path($this->studly($path, $ds), $ds);
    }

    /**
     * Get a well-formatted StudlyCase namespace for a module, with an optional additional path.
     */
    public function module_namespace(string $module, ?string $path = null): string
    {
        $module_namespace = config('modules.namespace', $this->path_namespace(config('modules.paths.modules'))).'\\'.($module);
        $module_namespace .= strlen($path) ? '\\'.$this->path_namespace($path) : '';

        return $this->studly_namespace($module_namespace);
    }

    /**
     * Get module app path.
     */
    public function module_app_path(string $module, ?string $path = null): string
    {
        return $this->module_path($module, $this->app_path($path));
    }

    /**
     * Get module app path.
     */
    public function module_app_base_path(string $module, ?string $path = null): string
    {
        return base_path($this->module_app_path($module, $path));
    }

    /**
     * Get a well-formatted module path.
     */
    public function module_path(?string $module = null, ?string $path = null): string
    {
        $module_path = $this->clean_path(config('modules.paths.modules', 'modules/')."/$module");
        $module_path .= strlen($path) ? $this->clean_path($path) : '';

        return $this->clean_path($module_path);
    }

    public function module_base_path(?string $module = null, ?string $path = null): string
    {
        return base_path($this->module_path($module, $path));
    }

    /**
     * Get a well-formatted namespace from a given path.
     */
    public function path_namespace(string $path): string
    {
        return Str::of($this->studly_path($path))->replace('/', '\\')->trim('\\');
    }

    /**
     * Get the app path basename.
     */
    public function app_path(?string $path = null): string
    {
        $default_app_path = 'app/';
        $app_path = $this->clean_path(empty($config = config('modules.paths.app')) ? $default_app_path : $config);
        if ($path) {
            $app_path = $this->check_app_path($app_path.$path);
        }

        return $this->clean_path($app_path);
    }

    /**
     * Check and update app/ path
     */
    public function check_app_path(string $path): ?string
    {
        $path = $this->clean_path($path);

        $default_app_path = 'app/';
        $app_path = $this->clean_path(empty($config = config('modules.paths.app')) ? $default_app_path : $config);
        $replaces = array_unique([$app_path, $default_app_path]);

        if (Str::startsWith($path, $replaces)) {
            do {
                foreach ($replaces as $replace) {
                    $path = Str::of($path)->replaceStart($replace, '');
                }
            } while (Str::of($path)->startsWith($replaces));

            $path = strlen($path) ? ($app_path.$path) : $app_path;

            return $this->clean_path($path);
        }

        return $this->clean_path($path);
    }

    /**
     * Tells whether the path is a regular app path
     */
    public function is_app_path(string $path): bool
    {
        return Str::startsWith($this->check_app_path($path), $this->app_path());
    }

    /**
     * Checks whether the app directory exists
     */
    public function app_path_exists(?string $path = null): bool
    {
        return file_exists($this->app_path($path));
    }
}
