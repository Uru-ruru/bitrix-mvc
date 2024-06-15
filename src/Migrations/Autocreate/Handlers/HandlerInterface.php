<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

/**
 * Interface HandlerInterface.
 */
interface HandlerInterface
{
    /**
     * Get migration name.
     */
    public function getName(): string;

    /**
     * Get template name.
     */
    public function getTemplate(): string;

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array;
}
