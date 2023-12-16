<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

/**
 * Class OnBeforeUserTypeAdd.
 */
class OnBeforeUserTypeAdd extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->fields = $params[0];
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_add_uf_{$this->fields['FIELD_NAME']}_to_entity_{$this->fields['ENTITY_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_add_uf';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'fields' => var_export($this->fields, true),
            'code' => "'".$this->fields['FIELD_NAME']."'",
            'entity' => "'".$this->fields['ENTITY_ID']."'",
        ];
    }
}
