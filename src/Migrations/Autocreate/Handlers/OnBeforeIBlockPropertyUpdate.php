<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

use Uru\BitrixMigrations\Exceptions\SkipHandlerException;

/**
 * Class OnBeforeIBlockPropertyUpdate.
 */
class OnBeforeIBlockPropertyUpdate extends BaseHandler implements HandlerInterface
{
    /**
     * Old property fields from DB.
     *
     * @var array
     */
    protected $dbFields;

    /**
     * Constructor.
     *
     * @throws SkipHandlerException
     */
    public function __construct(array $params)
    {
        $this->fields = $params[0];

        $this->dbFields = $this->collectPropertyFieldsFromDB();

        if (!$this->propertyHasChanged() || !$this->fields['IBLOCK_ID']) {
            throw new SkipHandlerException();
        }
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_update_iblock_element_property_{$this->fields['CODE']}_in_ib_{$this->fields['IBLOCK_ID']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_update_iblock_element_property';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'fields' => var_export($this->fields, true),
            'iblockId' => $this->fields['IBLOCK_ID'],
            'code' => "'".$this->fields['CODE']."'",
        ];
    }

    /**
     * Collect property fields from DB and convert them to format that can be compared from user input.
     */
    protected function collectPropertyFieldsFromDB(): array
    {
        $fields = \CIBlockProperty::getByID($this->fields['ID'])->fetch();
        $fields['VALUES'] = [];

        $filter = [
            'IBLOCK_ID' => $this->fields['IBLOCK_ID'],
            'PROPERTY_ID' => $this->fields['ID'],
        ];
        $sort = [
            'SORT' => 'ASC',
            'VALUE' => 'ASC',
        ];

        $propertyEnums = \CIBlockPropertyEnum::GetList($sort, $filter);
        while ($v = $propertyEnums->GetNext()) {
            $fields['VALUES'][$v['ID']] = [
                'ID' => $v['ID'],
                'VALUE' => $v['VALUE'],
                'SORT' => $v['SORT'],
                'XML_ID' => $v['XML_ID'],
                'DEF' => $v['DEF'],
            ];
        }

        return $fields;
    }

    /**
     * Determine if property has been changed.
     */
    protected function propertyHasChanged(): bool
    {
        foreach ($this->dbFields as $field => $value) {
            if (isset($this->fields[$field]) && ($this->fields[$field] != $value)) {
                return true;
            }
        }

        return false;
    }
}
