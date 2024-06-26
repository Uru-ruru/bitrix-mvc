<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Uru\BitrixMigrations\Exceptions\SkipHandlerException;

/**
 * Class OnBeforeIBlockUpdate.
 */
class OnBeforeIBlockUpdate extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @throws SkipHandlerException
     */
    public function __construct(array $params)
    {
        $this->fields = $params[0];

        // Если кода нет то миграция создастся битая.
        // Еще это позволяет решить проблему с тем что создается лишняя миграция для торгового каталога
        // когда обновляют связанный с ним инфоблок.
        if (!$this->fields['CODE']) {
            throw new SkipHandlerException();
        }
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_update_iblock_{$this->fields['CODE']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_update_iblock';
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
