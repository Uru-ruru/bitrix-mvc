<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Bitrix\Main\Entity\Event;

class OnBeforeHLBlockAdd extends BaseHandler implements HandlerInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->event = $params[0];

        $this->fields = $this->event->getParameter('fields');
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return 'auto_add_hlblock_'.$this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_add_hlblock';
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
