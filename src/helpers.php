<?php

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Vite as ViteFacade;
use Nwidart\Modules\Traits\PathNamespace;

final class Helper
{
    use PathNamespace;
}

if (! function_exists('module_path')) {
    function module_path(string $name, ?string $path = null)
    {
        $helper = new Helper();
        $module = app('modules')->findOrFail($name);

        return $helper->module_path($module->name, $path);
    }
}

if (! function_exists('module_app_path')) {
    function module_app_path(string $name, ?string $path = null)
    {
        $helper = new Helper();
        $module = app('modules')->findOrFail($name);

        return $helper->module_app_path($module->name, $path);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath().'/config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return app()->make('path.public').($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (! function_exists('module_vite')) {
    /**
     * support for vite
     */
    function module_vite($module, $asset): Vite
    {
        return ViteFacade::useHotFile(storage_path('vite.hot'))->useBuildDirectory($module)->withEntryPoints([$asset]);
    }
}
