<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Uru\BitrixMigrations\Exceptions\StopHandlerException;

class OnBeforeGroupUpdate extends BaseHandler implements HandlerInterface
{
    /**
     * Bitrix group id.
     *
     * @var int
     */
    protected $id;

    /**
     * Constructor.
     *
     * @throws StopHandlerException
     */
    public function __construct(array $params)
    {
        $this->id = $params[0];
        $this->fields = $params[1];

        if (!$this->fields['STRING_ID']) {
            throw new StopHandlerException('Code is required to create a migration');
        }
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_update_group_{$this->fields['STRING_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_update_group';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'fields' => var_export($this->fields, true),
            'id' => $this->id,
        ];
    }
}
