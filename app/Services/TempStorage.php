<?php

namespace App\Services;

class TempStorage
{
    public $storage = [];

    public function store($key, $value)
    {
        $this->storage[$key] = $value;
    }

    public function get($key)
    {
        return $this->storage[$key] ?? null;
    }

    public function has($key): bool
    {
        return isset($this->storage[$key]);
    }

    public function clear($key)
    {
        unset($this->storage[$key]);
    }
}
