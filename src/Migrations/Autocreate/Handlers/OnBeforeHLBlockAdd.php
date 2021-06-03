<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Uru\BitrixMigrations\Exceptions\SkipHandlerException;
use Bitrix\Main\Entity\Event;

class OnBeforeHLBlockAdd extends BaseHandler implements HandlerInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * Constructor.
     *
     * @param array $params
     *
     * @throws SkipHandlerException
     */
    public function __construct(array $params)
    {
        $this->event = $params[0];

        $this->fields = $this->event->getParameter('fields');
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'auto_add_hlblock_' . $this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return 'auto_add_hlblock';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace(): array
    {
        return [
            'fields' => var_export($this->fields, true),
        ];
    }
}
