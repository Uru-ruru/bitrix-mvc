<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

/**
 * Class OnBeforeUserTypeDelete.
 */
class OnBeforeUserTypeDelete extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->fields = is_array($params[0]) ? $params[0] : \CUserTypeEntity::getByID($params[0]);
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_delete_uf_{$this->fields['FIELD_NAME']}_from_entity_{$this->fields['ENTITY_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_delete_uf';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'iblockId' => $this->fields['IBLOCK_ID'],
            'code' => "'".$this->fields['FIELD_NAME']."'",
            'entity' => "'".$this->fields['ENTITY_ID']."'",
            'fields' => var_export($this->fields, true),
        ];
    }
}
