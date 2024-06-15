<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Event;

class OnBeforeHLBlockDelete extends BaseHandler implements HandlerInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * HLBlock id.
     *
     * @var int
     */
    protected $id;

    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->event = $params[0];

        $eventParams = $this->event->getParameters();

        $this->id = $eventParams['id']['ID'];
        $this->fields = HighloadBlockTable::getById($this->id)->fetch();
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return 'auto_delete_hlblock_'.$this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_delete_hlblock';
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
