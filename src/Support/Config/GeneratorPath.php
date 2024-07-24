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
            $this->path = $path = $this->check_app_path($config['path']);
            $this->generate = $config['generate'];
            $this->namespace = $config['namespace'] ?? $this->path_namespace($this->check_app_path($path));
        } elseif (strlen($config)) {
            $this->path = $path = $this->check_app_path($config);
            $this->generate = (bool) $config;
            $this->namespace = $this->path_namespace($this->check_app_path($path));
        }
    }

    public function getPath()
    {
        return $this->check_app_path($this->path);
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
