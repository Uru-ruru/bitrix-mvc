<?php


namespace Uru\BitrixMigrations\Constructors;


use CUserTypeEntity;
use Exception;
use Uru\BitrixMigrations\Helpers;
use Uru\BitrixMigrations\Logger;

/**
 * Class UserField
 * @package Uru\BitrixMigrations\Constructors
 */
class UserField
{
    use FieldConstructor;

    /**
     * Добавить UF
     * @throws Exception
     */
    public function add(): int
    {
        $uf = new CUserTypeEntity();
        $result = $uf->Add($this->getFieldsWithDefault());

        if (!$result) {
            global $APPLICATION;
            throw new Exception($APPLICATION->GetException());
        }

        Logger::log("Добавлен UF {$this->fields['FIELD_NAME']} для {$this->fields['ENTITY_ID']}", Logger::COLOR_GREEN);

        return $result;
    }

    /**
     * Обновить UF
     * @param $id
     * @throws Exception
     */
    public function update($id): void
    {
        $uf = new CUserTypeEntity();
        $result = $uf->Update($id, $this->fields);

        if (!$result) {
            global $APPLICATION;
            throw new Exception($APPLICATION->GetException());
        }

        Logger::log("Обновлен UF {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить UF
     * @param $id
     * @throws Exception
     */
    public static function delete($id): void
    {
        $result = (new CUserTypeEntity())->Delete($id);

        if (!$result) {
            global $APPLICATION;
            throw new Exception($APPLICATION->GetException());
        }

        Logger::log("Удален UF $id", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления UF по умолчанию
     * @param string $entityId Идентификатор сущности
     * @param string $fieldName Код поля.
     * @return $this
     */
    public function constructDefault(string $entityId, string $fieldName)
    {
        return $this->setEntityId($entityId)->setFieldName($fieldName)->setUserType('string');
    }

    /**
     * Идентификатор сущности, к которой будет привязано свойство.
     * @param string $entityId
     * @return $this
     */
    public function setEntityId(string $entityId)
    {
        $this->fields['ENTITY_ID'] = $entityId;

        return $this;
    }

    /**
     * Код поля. Всегда должно начинаться с UF_
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName(string $fieldName)
    {
        $this->fields['FIELD_NAME'] = static::prepareUf($fieldName);

        return $this;
    }

    /**
     * тип пользовательского свойства
     * @param string $userType
     * @return $this
     */
    public function setUserType(string $userType)
    {
        $this->fields['USER_TYPE_ID'] = $userType;

        return $this;
    }

    /**
     * тип нового пользовательского свойства HL
     * @param string $table_name
     * @param string $showField
     * @return $this
     */
    public function setUserTypeHL(string $table_name, string $showField)
    {
        $linkId = Helpers::getHlId($table_name);
        $this->setUserType('hlblock')->setSettings([
            'HLBLOCK_ID' => Helpers::getHlId($table_name),
            'HLFIELD_ID' => Helpers::getFieldId(Constructor::objHLBlock($linkId), static::prepareUf($showField)),
        ]);

        return $this;
    }

    /**
     * тип нового пользовательского свойства "связь с разелом ИБ"
     * @param string $iblockId
     * @return $this
     */
    public function setUserTypeIblockSection(string $iblockId)
    {
        $this->setUserType('iblock_section')->setSettings([
            'IBLOCK_ID' => $iblockId,
        ]);

        return $this;
    }

    /**
     * тип нового пользовательского свойства "связь с элементом ИБ"
     * @param string $iblockId
     * @return $this
     */
    public function setUserTypeIblockElement(string $iblockId)
    {
        $this->setUserType('iblock_element')->setSettings([
            'IBLOCK_ID' => $iblockId,
        ]);

        return $this;
    }

    /**
     * XML_ID пользовательского свойства. Используется при выгрузке в качестве названия поля
     * @param string $xmlId
     * @return $this
     */
    public function setXmlId(string $xmlId)
    {
        $this->fields['XML_ID'] = $xmlId;

        return $this;
    }

    /**
     * Сортировка
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort)
    {
        $this->fields['SORT'] = $sort;

        return $this;
    }

    /**
     * Является поле множественным или нет
     * @param bool $multiple
     * @return $this
     */
    public function setMultiple(bool $multiple)
    {
        $this->fields['MULTIPLE'] = $multiple ? 'Y' : 'N';

        return $this;
    }

    /**
     * Обязательное или нет свойство
     * @param bool $mandatory
     * @return $this
     */
    public function setMandatory(bool $mandatory)
    {
        $this->fields['MANDATORY'] = $mandatory ? 'Y' : 'N';

        return $this;
    }

    /**
     * Показывать в фильтре списка. Возможные значения: не показывать = N, точное совпадение = I, поиск по маске = E, поиск по подстроке = S
     * @param string $showInFilter
     * @return $this
     */
    public function setShowFilter(string $showInFilter)
    {
        $this->fields['SHOW_FILTER'] = $showInFilter;

        return $this;
    }

    /**
     * Не показывать в списке. Если передать какое-либо значение, то будет считаться, что флаг выставлен.
     * @param bool $showInList
     * @return $this
     */
    public function setShowInList(bool $showInList)
    {
        $this->fields['SHOW_IN_LIST'] = $showInList ? 'Y' : '';

        return $this;
    }

    /**
     * Пустая строка разрешает редактирование. Если передать какое-либо значение, то будет считаться, что флаг выставлен.
     * @param bool $editInList
     * @return $this
     */
    public function setEditInList(bool $editInList)
    {
        $this->fields['EDIT_IN_LIST'] = $editInList ? 'Y' : '';

        return $this;
    }

    /**
     * Значения поля участвуют в поиске
     * @param bool $isSearchable
     * @return $this
     */
    public function setIsSearchable(bool $isSearchable = false)
    {
        $this->fields['IS_SEARCHABLE'] = $isSearchable ? 'Y' : 'N';

        return $this;
    }

    /**
     * Дополнительные настройки поля (зависят от типа). В нашем случае для типа string
     * @param array $settings
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->fields['SETTINGS'] = array_merge((array)$this->fields['SETTINGS'], $settings);

        return $this;
    }

    /**
     * Языковые фразы
     * @param string $lang
     * @param string $text
     * @return $this
     */
    public function setLangDefault(string $lang, string $text)
    {
        $this->setLangForm($lang, $text);
        $this->setLangColumn($lang, $text);
        $this->setLangFilter($lang, $text);

        return $this;
    }

    /**
     * Текст "Заголовок в списке"
     * @param string $lang
     * @param string $text
     * @return $this
     */
    public function setLangForm(string $lang, string $text)
    {
        $this->fields['EDIT_FORM_LABEL'][$lang] = $text;

        return $this;
    }

    /**
     * Текст "Заголовок в списке"
     * @param string $lang
     * @param string $text
     * @return $this
     */
    public function setLangColumn(string $lang, string $text)
    {
        $this->fields['LIST_COLUMN_LABEL'][$lang] = $text;

        return $this;
    }

    /**
     * Текст "Подпись фильтра в списке"
     * @param string $lang
     * @param string $text
     * @return $this
     */
    public function setLangFilter(string $lang, string $text)
    {
        $this->fields['LIST_FILTER_LABEL'][$lang] = $text;

        return $this;
    }

    /**
     * Текст "Помощь"
     * @param string $lang
     * @param string $text
     * @return $this
     */
    public function setLangHelp(string $lang, string $text)
    {
        $this->fields['HELP_MESSAGE'][$lang] = $text;

        return $this;
    }

    /**
     * Текст "Сообщение об ошибке (не обязательное)"
     * @param string $lang
     * @param string $text
     * @return $this
     */
    public function setLangError(string $lang, string $text)
    {
        $this->fields['ERROR_MESSAGE'][$lang] = $text;

        return $this;
    }

    /**
     * @param $name
     * @return mixed|string
     */
    protected static function prepareUf($name)
    {
        if (substr($name, 0, 3) != 'UF_') {
            $name = "UF_{$name}";
        }

        return $name;
    }
}
