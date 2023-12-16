<?php

namespace Uru\BitrixCacher;

/**
 * Class PhpCache.
 */
class PhpCache
{
    protected array $storage = [];

    /**
     * @var $this
     */
    private static $instance;

    /**
     * PhpCache constructor.
     */
    private function __construct() {}

    protected function __clone() {}

    /**
     * @return $this
     */
    public static function getInstance(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function put($key, $value): PhpCache
    {
        $this->storage[$key] = $value;

        return $this;
    }

    public function has($key): bool
    {
        return isset($this->storage[$key]);
    }

    public function get($key): mixed
    {
        return $this->storage[$key] ?? null;
    }

    public function forget($key): PhpCache
    {
        unset($this->storage[$key]);

        return $this;
    }

    public function flush(): PhpCache
    {
        $this->storage = [];

        return $this;
    }

    public function all(): array
    {
        return $this->storage;
    }
}
