<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Event;
use Uru\BitrixMigrations\Exceptions\SkipHandlerException;

class OnBeforeHLBlockUpdate extends BaseHandler implements HandlerInterface
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
     *
     * @throws SkipHandlerException
     */
    public function __construct(array $params)
    {
        $this->event = $params[0];

        $eventParams = $this->event->getParameters();

        $this->fields = $eventParams['fields'];
        $this->id = $eventParams['id']['ID'];

        if (!$this->fieldsHaveBeenChanged()) {
            throw new SkipHandlerException();
        }
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return 'auto_update_hlblock_'.$this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_update_hlblock';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'id' => $this->id,
            'fields' => var_export($this->fields, true),
        ];
    }

    /**
     * Determine if fields have been changed.
     */
    protected function fieldsHaveBeenChanged(): bool
    {
        $old = HighloadBlockTable::getById($this->id)->fetch();
        $new = $this->fields + ['ID' => (string) $this->id];

        return $new != $old;
    }
}
