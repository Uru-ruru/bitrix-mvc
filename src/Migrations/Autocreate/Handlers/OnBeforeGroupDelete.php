<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Uru\BitrixMigrations\Exceptions\StopHandlerException;
use CGroup;

class OnBeforeGroupDelete extends BaseHandler implements HandlerInterface
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
     * @param array $params
     *
     */
    public function __construct(array $params)
    {
        $this->id = $params[0];

        $this->fields = CGroup::GetByID($this->id)->fetch();
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName(): string
    {
        return "auto_delete_group_{$this->fields['STRING_ID']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return 'auto_delete_group';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
