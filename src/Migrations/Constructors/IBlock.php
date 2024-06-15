<?php

namespace Uru\BitrixMigrations\Constructors;

use Uru\BitrixMigrations\Logger;

/**
 * Class IBlock.
 */
class IBlock
{
    use FieldConstructor;

    /**
     * Добавить инфоблок.
     *
     * @throws \Exception
     */
    public function add(): int
    {
        $obj = new \CIBlock();

        $iblockId = $obj->Add($this->getFieldsWithDefault());
        if (!$iblockId) {
            throw new \RuntimeException($obj->LAST_ERROR);
        }

        Logger::log("Добавлен инфоблок {$this->fields['CODE']}", Logger::COLOR_GREEN);

        return $iblockId;
    }

    /**
     * Обновить инфоблок.
     *
     * @param mixed $id
     *
     * @throws \Exception
     */
    public function update($id): void
    {
        $obj = new \CIBlock();
        if (!$obj->Update($id, $this->fields)) {
            throw new \RuntimeException($obj->LAST_ERROR);
        }

        Logger::log("Обновлен инфоблок {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить инфоблок.
     *
     * @param mixed $id
     *
     * @throws \Exception
     */
    public static function delete($id): void
    {
        if (!\CIBlock::Delete($id)) {
            throw new \RuntimeException('Ошибка при удалении инфоблока');
        }

        Logger::log("Удален инфоблок {$id}", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления инфоблока по умолчанию.
     *
     * @param mixed $name
     * @param mixed $code
     * @param mixed $iblock_type_id
     *
     * @return $this
     */
    public function constructDefault($name, $code, $iblock_type_id): static
    {
        return $this->setName($name)->setCode($code)->setIblockTypeId($iblock_type_id);
    }

    /**
     * ID сайта.
     *
     * @return $this
     */
    public function setSiteId(string $siteId): static
    {
        $this->fields['SITE_ID'] = $siteId;

        return $this;
    }

    /**
     * Символьный идентификатор.
     *
     * @return $this
     */
    public function setCode(string $code): static
    {
        $this->fields['CODE'] = $code;

        return $this;
    }

    /**
     * Внешний код.
     *
     * @return $this
     */
    public function setXmlId(string $xml_id): static
    {
        $this->fields['XML_ID'] = $xml_id;

        return $this;
    }

    /**
     * Код типа инфоблока.
     *
     * @return $this
     */
    public function setIblockTypeId(string $iblockTypeId): static
    {
        $this->fields['IBLOCK_TYPE_ID'] = $iblockTypeId;

        return $this;
    }

    /**
     * Название.
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->fields['NAME'] = $name;

        return $this;
    }

    /**
     * Флаг активности.
     *
     * @return $this
     */
    public function setActive(bool $active = true): static
    {
        $this->fields['ACTIVE'] = $active ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индекс сортировки.
     *
     * @return $this
     */
    public function setSort(int $sort = 500): static
    {
        $this->fields['SORT'] = $sort;

        return $this;
    }

    /**
     * Шаблон URL-а к странице для публичного просмотра списка элементов информационного блока.
     *
     * @return $this
     */
    public function setListPageUrl(string $listPageUrl): static
    {
        $this->fields['LIST_PAGE_URL'] = $listPageUrl;

        return $this;
    }

    /**
     * Шаблон URL-а к странице для просмотра раздела.
     *
     * @return $this
     */
    public function setSectionPageUrl(string $sectionPageUrl): static
    {
        $this->fields['SECTION_PAGE_URL'] = $sectionPageUrl;

        return $this;
    }

    /**
     * Канонический URL элемента.
     *
     * @return $this
     */
    public function setCanonicalPageUrl(string $canonicalPageUrl): static
    {
        $this->fields['CANONICAL_PAGE_URL'] = $canonicalPageUrl;

        return $this;
    }

    /**
     * URL детальной страницы элемента.
     *
     * @return $this
     */
    public function setDetailPageUrl(string $detailPageUrl): static
    {
        $this->fields['DETAIL_PAGE_URL'] = $detailPageUrl;

        return $this;
    }

    /**
     * Устанавливает значения по умолчанию для страниц инфоблока, раздела и деталей элемента
     * (как при создании через административный интерфейс или с ЧПУ).
     *
     * Для использовании ЧПУ рекомендуется сделать обязательными для заполнения символьный код
     * элементов и разделов инфоблока.
     *
     * @param bool $sef sef Использовать ли ЧПУ (понадобится добавить правило в urlrewrite)
     */
    public function setDefaultUrls(bool $sef = false): IBlock
    {
        if (true === $sef) {
            $prefix = '#SITE_DIR#/#IBLOCK_TYPE_ID#/#IBLOCK_CODE#/';
            $this
                ->setListPageUrl($prefix)
                ->setSectionPageUrl("{$prefix}#SECTION_CODE_PATH#/")
                ->setDetailPageUrl("{$prefix}#SECTION_CODE_PATH#/#ELEMENT_CODE#/")
            ;
        } else {
            $prefix = '#SITE_DIR#/#IBLOCK_TYPE_ID#';
            $this
                ->setListPageUrl("{$prefix}/index.php?ID=#IBLOCK_ID#")
                ->setSectionPageUrl("{$prefix}/list.php?SECTION_ID=#SECTION_ID#")
                ->setDetailPageUrl("{$prefix}/detail.php?ID=#ELEMENT_ID#")
            ;
        }

        return $this;
    }

    /**
     * Код картинки в таблице файлов.
     *
     * @return $this
     */
    public function setPicture(array $picture)
    {
        $this->fields['PICTURE'] = $picture;

        return $this;
    }

    /**
     * Описание.
     *
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->fields['DESCRIPTION'] = $description;

        return $this;
    }

    /**
     * Тип описания (text/html).
     *
     * @return $this
     */
    public function setDescriptionType(string $descriptionType = 'text')
    {
        $this->fields['DESCRIPTION_TYPE'] = $descriptionType;

        return $this;
    }

    /**
     * Разрешен экспорт в RSS динамически.
     *
     * @return $this
     */
    public function setRssActive(bool $rssActive = true)
    {
        $this->fields['RSS_ACTIVE'] = $rssActive ? 'Y' : 'N';

        return $this;
    }

    /**
     * Время жизни RSS и интервал между генерациями файлов RSS (при включенном RSS_FILE_ACTIVE или RSS_YANDEX_ACTIVE) (часов).
     *
     * @return $this
     */
    public function setRssTtl(int $rssTtl = 24)
    {
        $this->fields['RSS_TTL'] = $rssTtl;

        return $this;
    }

    /**
     * Прегенерировать выгрузку в файл.
     *
     * @return $this
     */
    public function setRssFileActive(bool $rssFileActive = false)
    {
        $this->fields['RSS_FILE_ACTIVE'] = $rssFileActive ? 'Y' : 'N';

        return $this;
    }

    /**
     * Количество экспортируемых в RSS файл элементов (при включенном RSS_FILE_ACTIVE).
     *
     * @return $this
     */
    public function setRssFileLimit(int $rssFileLimit)
    {
        $this->fields['RSS_FILE_LIMIT'] = $rssFileLimit;

        return $this;
    }

    /**
     * За сколько последних дней экспортировать в RSS файл. (при включенном RSS_FILE_ACTIVE). -1 без ограничения по дням.
     *
     * @return $this
     */
    public function setRssFileDays(int $rssFileDays)
    {
        $this->fields['RSS_FILE_DAYS'] = $rssFileDays;

        return $this;
    }

    /**
     * Экспортировать в RSS файл в формате для yandex.
     *
     * @return $this
     */
    public function setRssYandexActive(bool $rssYandexActive = false)
    {
        $this->fields['RSS_YANDEX_ACTIVE'] = $rssYandexActive ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индексировать для поиска элементы информационного блока.
     *
     * @return $this
     */
    public function setIndexElement(bool $indexElement = true)
    {
        $this->fields['INDEX_ELEMENT'] = $indexElement ? 'Y' : 'N';

        return $this;
    }

    /**
     * Индексировать для поиска разделы информационного блока.
     *
     * @return $this
     */
    public function setIndexSection(bool $indexSection = false)
    {
        $this->fields['INDEX_SECTION'] = $indexSection ? 'Y' : 'N';

        return $this;
    }

    /**
     * Режим отображения списка элементов в административном разделе (S|C).
     *
     * @return $this
     */
    public function setListMode(string $listMode)
    {
        $this->fields['LIST_MODE'] = $listMode;

        return $this;
    }

    /**
     * Режим проверки прав доступа (S|E).
     *
     * @return $this
     */
    public function setRightsMode(string $rightsMode = 'S')
    {
        $this->fields['RIGHTS_MODE'] = $rightsMode;

        return $this;
    }

    /**
     * Признак наличия привязки свойств к разделам (Y|N).
     *
     * @return $this
     */
    public function setSectionProperty(string $sectionProperty)
    {
        $this->fields['SECTION_PROPERTY'] = $sectionProperty;

        return $this;
    }

    /**
     * Признак наличия фасетного индекса (N|Y|I).
     *
     * @return $this
     */
    public function setPropertyIndex(string $propertyIndex)
    {
        $this->fields['PROPERTY_INDEX'] = $propertyIndex;

        return $this;
    }

    /**
     * Служебное поле для процедуры конвертации места хранения значений свойств инфоблока.
     *
     * @return $this
     */
    public function setLastConvElement(int $lastConvElement)
    {
        $this->fields['LAST_CONV_ELEMENT'] = $lastConvElement;

        return $this;
    }

    /**
     * Служебное поле для установки прав для разных групп на доступ к информационному блоку.
     *
     * @param array $groupId Массив соответствий кодов групп правам доступа
     *
     * @return $this
     */
    public function setGroupId(array $groupId)
    {
        $this->fields['GROUP_ID'] = $groupId;

        return $this;
    }

    /**
     * Служебное поле для привязки к группе социальной сети.
     *
     * @return $this
     */
    public function setSocnetGroupId(int $socnetGroupId)
    {
        $this->fields['SOCNET_GROUP_ID'] = $socnetGroupId;

        return $this;
    }

    /**
     * Инфоблок участвует в документообороте (Y|N).
     *
     * @return $this
     */
    public function setWorkflow(bool $workflow = true)
    {
        $this->fields['WORKFLOW'] = $workflow ? 'Y' : 'N';

        return $this;
    }

    /**
     * Инфоблок участвует в бизнес-процессах (Y|N).
     *
     * @return $this
     */
    public function setBizProc(bool $bizproc = false)
    {
        $this->fields['BIZPROC'] = $bizproc ? 'Y' : 'N';

        return $this;
    }

    /**
     * Флаг выбора интерфейса отображения привязки элемента к разделам (D|L|P).
     *
     * @return $this
     */
    public function setSectionChooser(string $sectionChooser)
    {
        $this->fields['SECTION_CHOOSER'] = $sectionChooser;

        return $this;
    }

    /**
     * Флаг хранения значений свойств элементов инфоблока (1 - в общей таблице | 2 - в отдельной).
     *
     * @return $this
     */
    public function setVersion(int $version = 1)
    {
        $this->fields['VERSION'] = $version;

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
     * Название элемента в единственном числе.
     *
     * @return $this
     */
    public function setMessElementName(string $message = 'Элемент')
    {
        $this->fields['ELEMENT_NAME'] = $message;

        return $this;
    }

    /**
     * Название элемента во множнственном числе.
     *
     * @return $this
     */
    public function setMessElementsName(string $message = 'Элементы')
    {
        $this->fields['ELEMENTS_NAME'] = $message;

        return $this;
    }

    /**
     * Действие по добавлению элемента.
     *
     * @return $this
     */
    public function setMessElementAdd(string $message = 'Добавить элемент')
    {
        $this->fields['ELEMENT_ADD'] = $message;

        return $this;
    }

    /**
     * Действие по редактированию/изменению элемента.
     *
     * @return $this
     */
    public function setMessElementEdit(string $message = 'Изменить элемент')
    {
        $this->fields['ELEMENT_EDIT'] = $message;

        return $this;
    }

    /**
     * Действие по удалению элемента.
     *
     * @return $this
     */
    public function setMessElementDelete(string $message = 'Удалить элемент')
    {
        $this->fields['ELEMENT_DELETE'] = $message;

        return $this;
    }

    /**
     * Название раздела в единственном числе.
     *
     * @return $this
     */
    public function setMessSectionName(string $message = 'Раздел')
    {
        $this->fields['SECTION_NAME'] = $message;

        return $this;
    }

    /**
     * Название раздела во множнственном числе.
     *
     * @return $this
     */
    public function setMessSectionsName(string $message = 'Разделы')
    {
        $this->fields['SECTIONS_NAME'] = $message;

        return $this;
    }

    /**
     * Действие по добавлению раздела.
     *
     * @return $this
     */
    public function setMessSectionAdd(string $message = 'Добавить раздел')
    {
        $this->fields['SECTION_ADD'] = $message;

        return $this;
    }

    /**
     * Действие по редактированию/изменению раздела.
     *
     * @return $this
     */
    public function setMessSectionEdit(string $message = 'Изменить раздел')
    {
        $this->fields['SECTION_EDIT'] = $message;

        return $this;
    }

    /**
     * Действие по удалению раздела.
     *
     * @return $this
     */
    public function setMessSectionDelete(string $message = 'Удалить раздел')
    {
        $this->fields['SECTION_DELETE'] = $message;

        return $this;
    }
}
