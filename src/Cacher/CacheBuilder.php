<?php

namespace Uru\BitrixCacher;

/**
 * Class CacheBuilder.
 */
class CacheBuilder
{
    protected ?string $key;

    protected ?float $minutes;

    protected string $initDir;

    protected string $baseDir;

    protected ?PhpCache $phpCache;

    protected bool $phpLayer;

    protected bool $onlyPhpLayer;

    /**
     * CacheBuilder constructor.
     */
    public function __construct()
    {
        $this->restoreDefaults();
    }

    /**
     * Setter for key.
     *
     * @param mixed $key
     */
    public function key($key): CacheBuilder
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Setter for time.
     *
     * @param mixed $seconds
     */
    public function seconds($seconds): CacheBuilder
    {
        $this->minutes = $seconds / 60;

        return $this;
    }

    /**
     * Setter for time.
     *
     * @param mixed $minutes
     */
    public function minutes($minutes): CacheBuilder
    {
        $this->minutes = $minutes;

        return $this;
    }

    /**
     * Setter for time.
     *
     * @param mixed $hours
     */
    public function hours($hours): CacheBuilder
    {
        $this->minutes = intval($hours * 60);

        return $this;
    }

    /**
     * Setter for time.
     *
     * @param mixed $days
     */
    public function days($days): CacheBuilder
    {
        $this->minutes = (int) ($days * 60 * 24);

        return $this;
    }

    /**
     * Setter for initDir.
     *
     * @param mixed $dir
     */
    public function initDir($dir): CacheBuilder
    {
        $this->initDir = $dir;

        return $this;
    }

    /**
     * Setter for initDir.
     *
     * @param mixed $dir
     */
    public function baseDir($dir): CacheBuilder
    {
        $this->baseDir = $dir;

        return $this;
    }

    public function execute(\Closure $callback): mixed
    {
        if ($this->phpLayer || $this->onlyPhpLayer) {
            $key = $this->constructPhpCacheKey();
            if ($this->phpCache->has($key)) {
                return $this->phpCache->get($key);
            }
        }

        if ($this->onlyPhpLayer) {
            return $this->executeWithPhpCache($callback);
        }

        if (is_null($this->key)) {
            throw new \LogicException('Key is not set.');
        }

        if (is_null($this->minutes)) {
            throw new \LogicException('Time is not set.');
        }

        $result = Cache::remember($this->key, $this->minutes, $callback, $this->initDir, $this->baseDir);
        if ($this->phpLayer) {
            $this->phpCache->put($this->constructPhpCacheKey(), $result);
        }

        return $result;
    }

    /**
     * @return $this
     */
    public function restoreDefaults(): CacheBuilder
    {
        $this->key = null;
        $this->minutes = null;
        $this->initDir = '/';
        $this->baseDir = 'cache';
        $this->phpLayer = false;
        $this->onlyPhpLayer = false;
        $this->phpCache = PhpCache::getInstance();

        return $this;
    }

    /**
     * Enable cache in php variable.
     *
     * @return $this
     */
    public function enablePhpLayer(bool $value = true): CacheBuilder
    {
        $this->phpLayer = $value;

        return $this;
    }

    /**
     * Enable cache in php variable.
     *
     * @return $this
     */
    public function onlyPhpLayer(bool $value = true): CacheBuilder
    {
        $this->onlyPhpLayer = $value;

        return $this;
    }

    protected function constructPhpCacheKey(): string
    {
        return json_encode([$this->key, $this->initDir, $this->baseDir]);
    }

    protected function executeWithPhpCache(\Closure $callback): mixed
    {
        $key = $this->constructPhpCacheKey();

        try {
            $result = $callback();
        } catch (AbortCacheException $e) {
            $result = null;
        }
        $this->phpCache->put($key, $result);

        return $result;
    }
}
