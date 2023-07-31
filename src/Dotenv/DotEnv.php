<?php

namespace Uru\DotEnv;

use Uru\DotEnv\Exceptions\MissingVariableException;

/**
 * Class DotEnv
 * @package Uru\DotEnv
 */
class DotEnv
{
    /**
     * Key-value storage.
     *
     * @var array
     */
    protected static array $variables = [];

    /**
     * Required variables.
     *
     * @var array
     */
    protected static array $required = [];

    /**
     * Were variables loaded?
     *
     * @var bool
     */
    protected static bool $isLoaded = false;

    /**
     * Load .env.php file or array.
     *
     * @param array|string $source
     *
     * @return void
     */
    public static function load(array|string $source): void
    {
        self::$variables = is_array($source) ? $source : require $source;
        self::$isLoaded = true;

        self::checkRequiredVariables();
    }

    /**
     * Copy all variables to putenv().
     *
     * @param string $prefix
     */
    public static function copyVarsToPutenv(string $prefix = 'PHP_'): void
    {
        foreach (self::all() as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }

            putenv("{$prefix}{$key}={$value}");
        }
    }

    /**
     * Copy all variables to $_ENV.
     */
    public static function copyVarsToEnv(): void
    {
        foreach (self::all() as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    /**
     * Copy all variables to $_SERVER.
     */
    public static function copyVarsToServer(): void
    {
        foreach (self::all() as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Get env variables.
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$variables;
    }

    /**
     * Get env variable.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $default;
    }

    /**
     * Set env variable.
     *
     * @param string|array $keys
     * @param mixed|null $value
     *
     * @return void
     */
    public static function set($keys, mixed $value = null): void
    {
        if (is_array($keys)) {
            self::$variables = array_merge(self::$variables, $keys);
        } else {
            self::$variables[$keys] = $value;
        }
    }

    /**
     * Set required variables.
     *
     * @param array $variables
     */
    public static function setRequired(array $variables): void
    {
        self::$required = $variables;

        if (self::$isLoaded) {
            self::checkRequiredVariables();
        }
    }

    /**
     * Delete all variables.
     *
     * @return void
     */
    public static function flush(): void
    {
        self::$variables = [];
        self::$isLoaded = false;
    }

    /**
     * Throw exception if any of required variables was not loaded.
     *
     * @return void
     * @throws MissingVariableException
     *
     */
    protected static function checkRequiredVariables(): void
    {
        foreach (self::$required as $key) {
            if (!isset(self::$variables[$key])) {
                throw new MissingVariableException(".env variable '{$key}' is missing");
            }
        }
    }
}
