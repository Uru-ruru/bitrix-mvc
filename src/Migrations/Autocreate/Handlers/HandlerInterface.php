<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

/**
 * Interface HandlerInterface
 * @package Uru\BitrixMigrations\Autocreate\Handlers
 */
interface HandlerInterface
{
    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate(): string;

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace(): array;
}
