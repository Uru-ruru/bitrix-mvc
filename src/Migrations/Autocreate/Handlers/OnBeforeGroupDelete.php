<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

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
     */
    public function __construct(array $params)
    {
        $this->id = $params[0];

        $this->fields = \CGroup::GetByID($this->id)->fetch();
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_delete_group_{$this->fields['STRING_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_delete_group';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
