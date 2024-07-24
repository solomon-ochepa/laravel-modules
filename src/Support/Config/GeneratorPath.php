<?php

namespace Nwidart\Modules\Support\Config;

use Nwidart\Modules\Traits\PathNamespace;

class GeneratorPath
{
    use PathNamespace;

    private string $path = '';

    private bool $generate = false;

    private string $namespace = '';

    public function __construct($config)
    {
        if (is_array($config)) {
            $this->path = $p = $this->clean_path($this->is_app_path($p = $config['path']) ? $this->app_path($p) : $p);
            $this->generate = $config['generate'];
            $this->namespace = $config['namespace'] ?? $this->path_namespace($this->is_app_path($p) ? $this->app_path($p) : $p);
        } elseif (strlen($config)) {
            $this->path = $p = $this->clean_path($this->is_app_path($p = $config) ? $this->app_path($p) : $p);
            $this->generate = (bool) $config;
            $this->namespace = $this->path_namespace($this->is_app_path($p) ? $this->app_path($p) : $p);
        }
    }

    public function getPath()
    {
        return $this->clean_path($this->is_app_path($p = $this->path) ? $this->app_path($p) : $p);
    }

    public function generate(): bool
    {
        return $this->generate;
    }

    public function getNamespace()
    {
        return $this->studly_namespace($this->namespace);
    }
}
