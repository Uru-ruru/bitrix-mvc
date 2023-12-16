<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Uru\BitrixMigrations\Exceptions\StopHandlerException;

class OnBeforeGroupAdd extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @throws StopHandlerException
     */
    public function __construct(array $params)
    {
        $this->fields = $params[0];

        if (!$this->fields['STRING_ID']) {
            throw new StopHandlerException('Code is required to create a migration');
        }
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_add_group_{$this->fields['STRING_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_add_group';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'fields' => var_export($this->fields, true),
        ];
    }
}
