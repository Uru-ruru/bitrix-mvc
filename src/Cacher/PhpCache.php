<?php

namespace Uru\BitrixCacher;

/**
 * Class PhpCache
 * @package Uru\BitrixCacher
 */
class PhpCache
{
    /**
     * @var array
     */
    protected array $storage = [];

    /**
     * @var $this
     */
    private static $instance = null;

    /**
     * PhpCache constructor.
     */
    private function __construct()
    {
    }

    /**
     *
     */
    protected function __clone()
    {
    }

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param $key
     * @param $value
     * @return PhpCache
     */
    public function put($key, $value): PhpCache
    {
        $this->storage[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->storage[$key] ?? null;
    }

    /**
     * @param $key
     * @return PhpCache
     */
    public function forget($key): PhpCache
    {
        unset($this->storage[$key]);

        return $this;
    }

    /**
     * @return PhpCache
     */
    public function flush(): PhpCache
    {
        $this->storage = [];

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->storage;
    }
}
