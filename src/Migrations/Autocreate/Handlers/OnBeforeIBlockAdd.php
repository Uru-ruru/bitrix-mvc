<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

class OnBeforeIBlockAdd extends BaseHandler implements HandlerInterface
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
        return "auto_add_iblock_{$this->fields['CODE']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_add_iblock';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'fields' => var_export($this->fields, true),
            'code' => "'".$this->fields['CODE']."'",
        ];
    }
}
