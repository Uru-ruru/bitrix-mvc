<?php

namespace Uru\BitrixMigrations\Constructors;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Uru\BitrixMigrations\Helpers;
use Uru\BitrixMigrations\Logger;

class HighloadBlock
{
    use FieldConstructor;

    public $lang;

    /**
     * Добавить HL.
     *
     * @throws \Exception
     */
    public function add(): int
    {
        $result = HighloadBlockTable::add($this->getFieldsWithDefault());

        if (!$result->isSuccess()) {
            throw new \RuntimeException(implode(', ', $result->getErrorMessages()));
        }

        foreach ($this->lang as $lid => $name) {
            HighloadBlockLangTable::add([
                'ID' => $result->getId(),
                'LID' => $lid,
                'NAME' => $name,
            ]);
        }

        Logger::log("Добавлен HL {$this->fields['NAME']}", Logger::COLOR_GREEN);

        return $result->getId();
    }

    /**
     * Обновить HL.
     *
     * @param mixed $table_name
     *
     * @throws \Exception
     */
    public function update($table_name): void
    {
        $id = Helpers::getHlId($table_name);
        $result = HighloadBlockTable::update($id, $this->fields);

        if (!$result->isSuccess()) {
            throw new \Exception(join(', ', $result->getErrorMessages()));
        }

        Logger::log("Обновлен HL {$table_name}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить HL.
     *
     * @param mixed $table_name
     *
     * @throws \Exception
     */
    public static function delete($table_name): void
    {
        $id = Helpers::getHlId($table_name);
        $result = HighloadBlockTable::delete($id);

        if (!$result->isSuccess()) {
            throw new \Exception(join(', ', $result->getErrorMessages()));
        }

        Logger::log("Удален HL {$table_name}", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления HL по умолчанию.
     *
     * @param string $name       Название highload-блока
     * @param string $table_name название таблицы с элементами highload-блока
     *
     * @return $this
     */
    public function constructDefault(string $name, string $table_name): static
    {
        return $this->setName($name)->setTableName($table_name);
    }

    /**
     * Название highload-блока.
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->fields['NAME'] = $name;

        return $this;
    }

    /**
     * Название таблицы с элементами highload-блока.
     *
     * @return $this
     */
    public function setTableName(string $table_name)
    {
        $this->fields['TABLE_NAME'] = $table_name;

        return $this;
    }

    /**
     * Установить локализацию.
     *
     * @param mixed $lang
     * @param mixed $text
     *
     * @return $this
     */
    public function setLang($lang, $text): static
    {
        $this->lang[$lang] = $text;

        return $this;
    }
}
