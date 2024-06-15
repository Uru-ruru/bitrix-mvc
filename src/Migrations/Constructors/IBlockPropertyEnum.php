<?php

namespace Uru\BitrixMigrations\Constructors;

use Uru\BitrixMigrations\Logger;

/**
 * Class IBlockPropertyEnum.
 */
class IBlockPropertyEnum
{
    use FieldConstructor;

    /**
     * Добавить значение списка.
     *
     * @throws \Exception
     */
    public function add(): int
    {
        $obj = new \CIBlockPropertyEnum();

        $property_enum_id = $obj->Add($this->getFieldsWithDefault());

        if (!$property_enum_id) {
            throw new \Exception('Ошибка добавления значения enum');
        }

        Logger::log("Добавлено значение списка enum {$this->fields['VALUE']}", Logger::COLOR_GREEN);

        return $property_enum_id;
    }

    /**
     * Обновить свойство инфоблока.
     *
     * @param mixed $id
     *
     * @throws \Exception
     */
    public function update($id): void
    {
        $obj = new \CIBlockPropertyEnum();
        if (!$obj->Update($id, $this->fields)) {
            throw new \Exception('Ошибка обновления значения enum');
        }

        Logger::log("Обновлено значение списка enum {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить свойство инфоблока.
     *
     * @param mixed $id
     *
     * @throws \Exception
     */
    public static function delete($id): void
    {
        if (!\CIBlockPropertyEnum::Delete($id)) {
            throw new \Exception('Ошибка при удалении значения enum');
        }

        Logger::log("Удалено значение списка enum {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления значения enum инфоблока по умолчанию.
     *
     * @return $this
     */
    public function constructDefault(string $xml_id, string $value, ?int $propertyId = null)
    {
        $this->setXmlId($xml_id)->setValue($value);

        if ($propertyId) {
            $this->setPropertyId($propertyId);
        }

        return $this;
    }

    /**
     * Код свойства.
     *
     * @return $this
     */
    public function setPropertyId(string $propertyId)
    {
        $this->fields['PROPERTY_ID'] = $propertyId;

        return $this;
    }

    /**
     * Внешний код.
     *
     * @return $this
     */
    public function setXmlId(string $xml_id)
    {
        $this->fields['XML_ID'] = $xml_id;

        return $this;
    }

    /**
     * Индекс сортировки.
     *
     * @return $this
     */
    public function setSort(int $sort = 500)
    {
        $this->fields['SORT'] = $sort;

        return $this;
    }

    /**
     * Значение варианта свойства.
     *
     * @return $this
     */
    public function setValue(string $value)
    {
        $this->fields['VALUE'] = $value;

        return $this;
    }

    /**
     * Значение варианта свойства.
     *
     * @return $this
     */
    public function setDef(bool $def)
    {
        $this->fields['DEF'] = $def ? 'Y' : 'N';

        return $this;
    }
}
