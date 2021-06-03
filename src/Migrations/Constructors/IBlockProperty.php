<?php


namespace Uru\BitrixMigrations\Constructors;


use CIBlockProperty;
use Exception;
use Uru\BitrixMigrations\Logger;

/**
 * Class IBlockProperty
 * @package Uru\BitrixMigrations\Constructors
 */
class IBlockProperty
{
    use FieldConstructor;

    /**
     * Добавить свойство инфоблока
     * @throws Exception
     */
    public function add(): int
    {
        $obj = new CIBlockProperty();

        $property_id = $obj->Add($this->getFieldsWithDefault());

        if (!$property_id) {
            throw new Exception($obj->LAST_ERROR);
        }

        Logger::log("Добавлено свойство инфоблока {$this->fields['CODE']}", Logger::COLOR_GREEN);

        return $property_id;
    }

    /**
     * Обновить свойство инфоблока
     * @param $id
     * @throws Exception
     */
    public function update($id)
    {
        $obj = new CIBlockProperty();
        if (!$obj->Update($id, $this->fields)) {
            throw new Exception($obj->LAST_ERROR);
        }

        Logger::log("Обновлено свойство инфоблока {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить свойство инфоблока
     * @param $id
     * @throws Exception
     */
    public static function delete($id)
    {
        if (!CIBlockProperty::Delete($id)) {
            throw new Exception('Ошибка при удалении свойства инфоблока');
        }

        Logger::log("Удалено свойство инфоблока {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления свойства инфоблока по умолчанию
     * @param string $code
     * @param string $name
     * @param int $iblockId
     * @return IBlockProperty
     */
    public function constructDefault(string $code, string $name, int $iblockId)
    {
        return $this->setPropertyType('S')->setCode($code)->setName($name)->setIblockId($iblockId);
    }

    /**
     * Символьный идентификатор.
     * @param string $code
     * @return $this
     */
    public function setCode(string $code)
    {
        $this->fields['CODE'] = $code;

        return $this;
    }

    /**
     * Внешний код.
     * @param string $xml_id
     * @return $this
     */
    public function setXmlId(string $xml_id)
    {
        $this->fields['XML_ID'] = $xml_id;

        return $this;
    }

    /**
     * Код информационного блока.
     * @param string $iblock_id
     * @return $this
     */
    public function setIblockId(string $iblock_id)
    {
        $this->fields['IBLOCK_ID'] = $iblock_id;

        return $this;
    }

    /**
     * Название.
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->fields['NAME'] = $name;

        return $this;
    }

    /**
     * Флаг активности
     * @param bool $active
     * @return $this
     */
    public function setActive(bool $active = true)
    {
        $this->fields['ACTIVE'] = $active ? 'Y' : 'N';

        return $this;
    }

    /**
     * Обязательное (Y|N).
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired(bool $isRequired = true)
    {
        $this->fields['IS_REQUIRED'] = $isRequired ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индекс сортировки.
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort = 500)
    {
        $this->fields['SORT'] = $sort;

        return $this;
    }

    /**
     * Тип свойства. Возможные значения: S - строка, N - число, F - файл, L - список, E - привязка к элементам, G - привязка к группам.
     * @param string $propertyType
     * @return $this
     */
    public function setPropertyType(string $propertyType = 'S')
    {
        $this->fields['PROPERTY_TYPE'] = $propertyType;

        return $this;
    }

    /**
     * Установить тип свойства "Список"
     * @param array $values массив доступных значений (можно собрать с помощью класса IBlockPropertyEnum)
     * @param string|null $listType Тип, может быть "L" - выпадающий список или "C" - флажки.
     * @param int|null $multipleCnt Количество строк в выпадающем списке
     * @return $this
     */
    public function setPropertyTypeList(array $values, ?string $listType = null, ?int $multipleCnt = null)
    {
        $this->setPropertyType('L');
        $this->fields['VALUES'] = $values;

        if (!is_null($listType)) {
            $this->setListType($listType);
        }

        if (!is_null($multipleCnt)) {
            $this->setMultipleCnt($multipleCnt);
        }

        return $this;
    }

    /**
     * Установить тип свойства "Файл"
     * @param string|null $fileType Список допустимых расширений (через запятую).
     * @return $this
     */
    public function setPropertyTypeFile(?string $fileType = null)
    {
        $this->setPropertyType('F');

        if (!is_null($fileType)) {
            $this->setFileType($fileType);
        }

        return $this;
    }

    /**
     * Установить тип свойства "привязка к элементам" или "привязка к группам"
     * @param string $property_type Тип свойства. Возможные значения: E - привязка к элементам, G - привязка к группам.
     * @param string $linkIblockId код информационного блока с элементами/группами которого и будут связано значение.
     * @return $this
     */
    public function setPropertyTypeIblock(string $property_type, string $linkIblockId)
    {
        $this->setPropertyType($property_type)->setLinkIblockId($linkIblockId);

        return $this;
    }

    /**
     * Установить тип свойства "справочник"
     * @param string $table_name таблица HL для связи
     * @return $this
     */
    public function setPropertyTypeHl(string $table_name)
    {
        $this->setPropertyType('S')->setUserType('directory')->setUserTypeSettings([
            'TABLE_NAME' => $table_name
        ]);

        return $this;
    }

    /**
     * Множественность (Y|N).
     * @param bool $multiple
     * @return $this
     */
    public function setMultiple(bool $multiple = false)
    {
        $this->fields['MULTIPLE'] = $multiple ? 'Y' : 'N';

        return $this;
    }

    /**
     * Количество строк в выпадающем списке для свойств типа "список".
     * @param int $multipleCnt
     * @return $this
     */
    public function setMultipleCnt(int $multipleCnt)
    {
        $this->fields['MULTIPLE_CNT'] = $multipleCnt;

        return $this;
    }

    /**
     * Значение свойства по умолчанию (кроме свойства типа список L).
     * @param string $defaultValue
     * @return $this
     */
    public function setDefaultValue(string $defaultValue)
    {
        $this->fields['DEFAULT_VALUE'] = $defaultValue;

        return $this;
    }

    /**
     * Количество строк в ячейке ввода значения свойства.
     * @param int $rowCount
     * @return $this
     */
    public function setRowCount(int $rowCount)
    {
        $this->fields['ROW_COUNT'] = $rowCount;

        return $this;
    }

    /**
     * Количество столбцов в ячейке ввода значения свойства.
     * @param int $colCount
     * @return $this
     */
    public function setColCount(int $colCount)
    {
        $this->fields['COL_COUNT'] = $colCount;

        return $this;
    }

    /**
     * Тип для свойства список (L). Может быть "L" - выпадающий список или "C" - флажки.
     * @param string $listType
     * @return $this
     */
    public function setListType(string $listType = 'L')
    {
        $this->fields['LIST_TYPE'] = $listType;

        return $this;
    }

    /**
     * Список допустимых расширений для свойств файл "F" (через запятую).
     * @param string $fileType
     * @return $this
     */
    public function setFileType(string $fileType)
    {
        $this->fields['FILE_TYPE'] = $fileType;

        return $this;
    }

    /**
     * Индексировать значения данного свойства.
     * @param bool $searchable
     * @return $this
     */
    public function setSearchable(bool $searchable = false)
    {
        $this->fields['SEARCHABLE'] = $searchable ? 'Y' : 'N';

        return $this;
    }

    /**
     * Выводить поля для фильтрации по данному свойству на странице списка элементов в административном разделе.
     * @param bool $filtrable
     * @return $this
     */
    public function setFiltrable(bool $filtrable = false)
    {
        $this->fields['FILTRABLE'] = $filtrable ? 'Y' : 'N';

        return $this;
    }

    /**
     * Для свойств типа привязки к элементам и группам задает код информационного блока с элементами/группами которого и будут связано значение.
     * @param int $linkIblockId
     * @return $this
     */
    public function setLinkIblockId(int $linkIblockId)
    {
        $this->fields['LINK_IBLOCK_ID'] = $linkIblockId;

        return $this;
    }

    /**
     * Признак наличия у значения свойства дополнительного поля описания. Только для типов S - строка, N - число и F - файл (Y|N).
     * @param bool $withDescription
     * @return $this
     */
    public function setWithDescription(bool $withDescription)
    {
        $this->fields['WITH_DESCRIPTION'] = $withDescription ? 'Y' : 'N';

        return $this;
    }

    /**
     * Идентификатор пользовательского типа свойства.
     * @param string $user_type
     * @return $this
     */
    public function setUserType(string $user_type)
    {
        $this->fields['USER_TYPE'] = $user_type;

        return $this;
    }

    /**
     * Идентификатор пользовательского типа свойства.
     * @param array $user_type_settings
     * @return $this
     */
    public function setUserTypeSettings(array $user_type_settings)
    {
        $this->fields['USER_TYPE_SETTINGS'] = array_merge((array)$this->fields['USER_TYPE_SETTINGS'], $user_type_settings);

        return $this;
    }

    /**
     * Подсказка
     * @param string $hint
     * @return $this
     */
    public function setHint(string $hint)
    {
        $this->fields['HINT'] = $hint;

        return $this;
    }
}
