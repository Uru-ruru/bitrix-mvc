<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

/**
 * Class OnBeforeIBlockPropertyDelete.
 */
class OnBeforeIBlockPropertyDelete extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->fields = \CIBlockProperty::getByID($params[0])->fetch();
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_delete_iblock_element_property_{$this->fields['CODE']}_in_ib_{$this->fields['IBLOCK_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_delete_iblock_element_property';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'iblockId' => $this->fields['IBLOCK_ID'],
            'code' => "'".$this->fields['CODE']."'",
        ];
    }
}
