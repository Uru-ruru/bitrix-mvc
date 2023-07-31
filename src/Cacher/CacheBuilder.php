<?php

namespace Uru\BitrixCacher;

use Closure;
use LogicException;

/**
 * Class CacheBuilder
 * @package Uru\BitrixCacher
 */
class CacheBuilder
{
    /**
     * @var string|null
     */
    protected ?string $key;

    /**
     * @var float|null
     */
    protected ?float $minutes;

    /**
     * @var string
     */
    protected string $initDir;

    /**
     * @var string
     */
    protected string $baseDir;

    /**
     * @var PhpCache|null
     */
    protected ?PhpCache $phpCache;

    /**
     * @var bool
     */
    protected bool $phpLayer;

    /**
     * @var bool
     */
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
     * @param $key
     * @return CacheBuilder
     */
    public function key($key): CacheBuilder
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Setter for time.
     * @param $seconds
     * @return CacheBuilder
     */
    public function seconds($seconds): CacheBuilder
    {
        $this->minutes = $seconds / 60;

        return $this;
    }

    /**
     * Setter for time.
     * @param $minutes
     * @return CacheBuilder
     */
    public function minutes($minutes): CacheBuilder
    {
        $this->minutes = $minutes;

        return $this;
    }

    /**
     * Setter for time.
     * @param $hours
     * @return CacheBuilder
     */
    public function hours($hours): CacheBuilder
    {
        $this->minutes = intval($hours * 60);

        return $this;
    }

    /**
     * Setter for time.
     * @param $days
     * @return CacheBuilder
     */
    public function days($days): CacheBuilder
    {
        $this->minutes = (int)($days * 60 * 24);

        return $this;
    }

    /**
     * Setter for initDir.
     * @param $dir
     * @return CacheBuilder
     */
    public function initDir($dir): CacheBuilder
    {
        $this->initDir = $dir;

        return $this;
    }

    /**
     * Setter for initDir.
     * @param $dir
     * @return CacheBuilder
     */
    public function baseDir($dir): CacheBuilder
    {
        $this->baseDir = $dir;

        return $this;
    }

    /**
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed
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
            throw new LogicException('Key is not set.');
        }

        if (is_null($this->minutes)) {
            throw new LogicException('Time is not set.');
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
     * @param bool $value
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
     * @param bool $value
     * @return $this
     */
    public function onlyPhpLayer(bool $value = true): CacheBuilder
    {
        $this->onlyPhpLayer = $value;

        return $this;
    }

    /**
     * @return string
     */
    protected function constructPhpCacheKey(): string
    {
        return json_encode([$this->key, $this->initDir, $this->baseDir]);
    }

    /**
     * @param Closure $callback
     * @return mixed
     */
    protected function executeWithPhpCache(Closure $callback): mixed
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
