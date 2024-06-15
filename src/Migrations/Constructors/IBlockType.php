<?php

namespace Uru\BitrixMigrations\Constructors;

use Uru\BitrixMigrations\Logger;

/**
 * Class IBlockType.
 */
class IBlockType
{
    use FieldConstructor;

    /**
     * Добавить тип инфоблока.
     *
     * @throws \Exception
     */
    public function add(): void
    {
        $obj = new \CIBlockType();
        if (!$obj->Add($this->getFieldsWithDefault())) {
            throw new \Exception($obj->LAST_ERROR);
        }

        Logger::log("Добавлен тип инфоблока {$this->fields['ID']}", Logger::COLOR_GREEN);
    }

    /**
     * Обновить тип инфоблока.
     *
     * @param mixed $id
     *
     * @throws \Exception
     */
    public function update($id): void
    {
        $obj = new \CIBlockType();
        if (!$obj->Update($id, $this->fields)) {
            throw new \Exception($obj->LAST_ERROR);
        }

        Logger::log("Обновлен тип инфоблока {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить тип инфоблока.
     *
     * @param mixed $id
     *
     * @throws \Exception
     */
    public static function delete($id): void
    {
        if (!\CIBlockType::Delete($id)) {
            throw new \Exception('Ошибка при удалении типа инфоблока');
        }

        Logger::log("Удален тип инфоблока {$id}", Logger::COLOR_GREEN);
    }

    /**
     * ID типа информационных блоков. Уникален.
     *
     * @return $this
     */
    public function setId(string $id)
    {
        $this->fields['ID'] = $id;

        return $this;
    }

    /**
     * Разделяются ли элементы блока этого типа по разделам.
     *
     * @return $this
     */
    public function setSections(bool $has = true)
    {
        $this->fields['SECTIONS'] = $has ? 'Y' : 'N';

        return $this;
    }

    /**
     * Полный путь к файлу-обработчику массива полей элемента перед сохранением на странице редактирования элемента.
     *
     * @return $this
     */
    public function setEditFileBefore(string $editFileBefore)
    {
        $this->fields['EDIT_FILE_BEFORE'] = $editFileBefore;

        return $this;
    }

    /**
     * Полный путь к файлу-обработчику вывода интерфейса редактирования элемента.
     *
     * @return $this
     */
    public function setEditFileAfter(string $editFileAfter)
    {
        $this->fields['EDIT_FILE_AFTER'] = $editFileAfter;

        return $this;
    }

    /**
     * Блоки данного типа экспортировать в RSS.
     *
     * @return $this
     */
    public function setInRss(bool $inRss = false)
    {
        $this->fields['IN_RSS'] = $inRss ? 'Y' : 'N';

        return $this;
    }

    /**
     * Порядок сортировки типа.
     *
     * @return $this
     */
    public function setSort(int $sort = 500)
    {
        $this->fields['SORT'] = $sort;

        return $this;
    }

    /**
     * Указать языковые фразы.
     *
     * @param string $lang ключ языка (ru)
     *
     * @return $this
     */
    public function setLang(string $lang, string $name, ?string $sectionName = null, ?string $elementName = null)
    {
        $setting = ['NAME' => $name];

        if ($sectionName) {
            $setting['SECTION_NAME'] = $sectionName;
        }
        if ($elementName) {
            $setting['ELEMENT_NAME'] = $elementName;
        }

        $this->fields['LANG'][$lang] = $setting;

        return $this;
    }
}
