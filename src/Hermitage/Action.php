<?php

namespace Uru\BitrixHermitage;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use CBitrixComponent;
use CBitrixComponentTemplate;
use CIBlock;
use InvalidArgumentException;

Loc::loadMessages(__FILE__);

class Action
{
    protected static array $panelButtons = [];

    protected static array $iblockElementArray = [];

    protected static array $iblockSectionArray = [];

    protected static array $hlblockIdsByTableName= [];

    /**
     * Get edit area id for specific type
     *
     * @param CBitrixComponentTemplate $template
     * @param $type
     * @param $element
     * @return string
     */
    public static function getEditArea(CBitrixComponentTemplate $template, $type, $element): string
    {
        $id = is_numeric($element) ? $element : $element['ID'];
        return $template->GetEditAreaId("{$type}_{$id}");
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @return string
     */
    public static function editIBlockElement(CBitrixComponentTemplate $template, $element): string
    {
        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($element)) {
            $element = static::prepareIBlockElementArrayById($element);
        }
        if (!$element["IBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and IBLOCK_ID');
        }

        $buttons = static::getIBlockElementPanelButtons($element);
        $link = $buttons["edit"]["edit_element"]["ACTION_URL"];

        $template->AddEditAction('iblock_element_' . $element['ID'], $link, CIBlock::GetArrayByID($element["IBLOCK_ID"], "ELEMENT_EDIT"));

        return static::areaForIBlockElement($template, $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @param string|null $confirm
     * @return string
     */
    public static function deleteIBlockElement(CBitrixComponentTemplate $template, $element, ?string $confirm = null): string
    {
        $confirm = $confirm ?: Loc::getMessage('URU_BITRIX_HERMITAGE_DELETE_IBLOCK_ELEMENT_CONFIRM');

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($element)) {
            $element = static::prepareIBlockElementArrayById($element);
        }

        if (!$element["IBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and IBLOCK_ID');
        }

        $buttons = static::getIBlockElementPanelButtons($element);
        $link = $buttons["edit"]["delete_element"]["ACTION_URL"];

        $template->AddDeleteAction('iblock_element_' . $element['ID'], $link, CIBlock::GetArrayByID($element["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => $confirm));

        return static::areaForIBlockElement($template, $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @return string
     */
    public static function editAndDeleteIBlockElement(CBitrixComponentTemplate $template, $element): string
    {
        static::editIBlockElement($template, $element);

        return static::deleteIBlockElement($template, $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @return string
     */
    public static function areaForIBlockElement(CBitrixComponentTemplate $template, $element): string
    {
        return static::getEditArea($template, 'iblock_element', $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $section
     * @return string
     */
    public static function editIBlockSection(CBitrixComponentTemplate $template, $section): string
    {
        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($section)) {
            $section = static::prepareIBlockSectionArrayById($section);
        }

        if (!$section["IBLOCK_ID"] || !$section['ID']) {
            throw new InvalidArgumentException('Section must include ID and IBLOCK_ID');
        }

        $buttons = static::getIBlockSectionPanelButtons($section);
        $link = $buttons["edit"]["edit_section"]["ACTION_URL"];

        $template->AddEditAction('iblock_section_' . $section['ID'], $link, CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_EDIT"));

        return static::areaForIBlockSection($template, $section);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $section
     * @param string|null $confirm
     * @return string
     */
    public static function deleteIBlockSection(CBitrixComponentTemplate $template, $section, ?string $confirm = null): string
    {
        $confirm = $confirm ?: Loc::getMessage("URU_BITRIX_HERMITAGE_DELETE_IBLOCK_SECTION_CONFIRM");

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($section)) {
            $section = static::prepareIBlockSectionArrayById($section);
        }

        if (!$section["IBLOCK_ID"] || !$section['ID']) {
            throw new InvalidArgumentException('Section must include ID and IBLOCK_ID');
        }

        $buttons = static::getIBlockSectionPanelButtons($section);
        $link = $buttons["edit"]["delete_section"]["ACTION_URL"];

        $template->AddDeleteAction('iblock_section_' . $section['ID'], $link, CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => $confirm));

        return static::areaForIBlockSection($template, $section);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $section
     * @return string
     */
    public static function editAndDeleteIBlockSection(CBitrixComponentTemplate $template, $section): string
    {
        static::editIBlockSection($template, $section);

        return static::deleteIBlockSection($template, $section);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $section
     * @return string
     */
    public static function areaForIBlockSection(CBitrixComponentTemplate $template, $section): string
    {
        return static::getEditArea($template, 'iblock_section', $section);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @param string|null $label
     * @return string
     * @throws ArgumentException
     */
    public static function editHLBlockElement(CBitrixComponentTemplate $template, $element, ?string $label = null): string
    {
        $label = $label ?: Loc::getMessage("URU_BITRIX_HERMITAGE_EDIT_HLBLOCK_ELEMENT_LABEL");

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (!$element["HLBLOCK_ID"] && $element["HLBLOCK_TABLE_NAME"]) {
            $element["HLBLOCK_ID"] = static::prepareHLBlockIdByTableName($element["HLBLOCK_TABLE_NAME"]);
        }

        if (!$element["HLBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and HLBLOCK_ID/HLBLOCK_TABLE_NAME');
        }

        $linkTemplate = '/bitrix/admin/highloadblock_row_edit.php?ENTITY_ID=%s&ID=%s&lang=ru&bxpublic=Y';
        $link = sprintf($linkTemplate, (int) $element["HLBLOCK_ID"], (int) $element["ID"]);

        $template->AddEditAction('hlblock_element_' . $element['ID'], $link, $label);

        return static::areaForHLBlockElement($template, $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @param string|null $label
     * @param string|null $confirm
     * @return string
     * @throws ArgumentException
     */
    public static function deleteHLBlockElement(CBitrixComponentTemplate $template, $element, ?string $label = null, ?string $confirm = null): string
    {
        $label = $label ?: Loc::getMessage('URU_BITRIX_HERMITAGE_DELETE_HLBLOCK_ELEMENT_LABEL');
        $confirm = $confirm ?: Loc::getMessage('URU_BITRIX_HERMITAGE_DELETE_HLBLOCK_ELEMENT_CONFIRM');

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (!$element["HLBLOCK_ID"] && $element["HLBLOCK_TABLE_NAME"]) {
            $element["HLBLOCK_ID"] = static::prepareHLBlockIdByTableName($element["HLBLOCK_TABLE_NAME"]);
        }

        if (!$element["HLBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and HLBLOCK_ID/HLBLOCK_TABLE_NAME');
        }

        $linkTemplate = '/bitrix/admin/highloadblock_row_edit.php?action=delete&ENTITY_ID=%s&ID=%s&lang=ru';
        $link = sprintf($linkTemplate, (int) $element["HLBLOCK_ID"], (int) $element["ID"]);

        $template->AddDeleteAction('hlblock_element_' . $element['ID'], $link, $label, array("CONFIRM" => $confirm));

        return static::areaForHLBlockElement($template, $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     *
     * @return string
     * @throws ArgumentException
     */
    public static function editAndDeleteHLBlockElement(CBitrixComponentTemplate $template, $element): string
    {
        static::editHLBlockElement($template, $element);
        static::deleteHLBlockElement($template, $element);

        return static::deleteHLBlockElement($template, $element);
    }

    /**
     * @param CBitrixComponentTemplate $template
     * @param $element
     * @return string
     */
    public static function areaForHLBlockElement(CBitrixComponentTemplate $template, $element): string
    {
        return static::getEditArea($template, 'hlblock_element', $element);
    }

    /**
     * @param CBitrixComponent|CBitrixComponentTemplate $componentOrTemplate
     * @param $iblockId
     * @param array $options
     */
    public static function addForIBlock($componentOrTemplate, $iblockId, array $options = [])
    {
        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return;
        }

        if ($componentOrTemplate instanceof CBitrixComponentTemplate) {
            $componentOrTemplate = $componentOrTemplate->__component;
        }

        $buttons = CIBlock::GetPanelButtons($iblockId, 0, 0, $options);
        $menu = CIBlock::GetComponentMenu($GLOBALS['APPLICATION']->GetPublicShowMode(), $buttons);

        $componentOrTemplate->addIncludeAreaIcons($menu);
    }

    /**
     * @param $element
     * @return array
     */
    protected static function getIBlockElementPanelButtons($element): array
    {
        if (!isset(static::$panelButtons['iblock_element'][$element['ID']])) {
            static::$panelButtons['iblock_element'][$element['ID']] = CIBlock::GetPanelButtons(
                $element["IBLOCK_ID"],
                $element['ID'],
                0,
                ['SECTION_BUTTONS' => false, 'SESSID' => false]
            );
        }

        return static::$panelButtons['iblock_element'][$element['ID']];
    }

    /**
     * @param $section
     * @return array
     */
    protected static function getIBlockSectionPanelButtons($section): array
    {
        if (!isset(static::$panelButtons['iblock_section'][$section['ID']])) {
            static::$panelButtons['iblock_section'][$section['ID']] = CIBlock::GetPanelButtons(
                $section["IBLOCK_ID"],
                0,
                $section['ID'],
                ['SESSID' => false]
            );
        }

        return static::$panelButtons['iblock_section'][$section['ID']];
    }

    /**
     * @param int $id
     * @return array
     */
    protected static function prepareIBlockElementArrayById(int $id): array
    {
        if (!$id) {
            return [];
        }

        if (empty(static::$iblockElementArray[$id])) {
            $connection = Application::getConnection();
            $el = $connection->query("SELECT ID, IBLOCK_ID FROM b_iblock_element WHERE ID = {$id}")->fetch();
            static::$iblockElementArray[$id] = $el ? $el : [];
        }

        return static::$iblockElementArray[$id];
    }

    /**
     * @param int $id
     * @return array
     */
    protected static function prepareIBlockSectionArrayById(int $id): array
    {
        if (!$id) {
            return [];
        }

        if (empty(static::$iblockSectionArray[$id])) {
            $connection = Application::getConnection();
            $el = $connection->query("SELECT ID, IBLOCK_ID FROM b_iblock_section WHERE ID = {$id}")->fetch();
            static::$iblockSectionArray[$id] = $el ? $el : [];
        }

        return static::$iblockSectionArray[$id];
    }

    /**
     * @param string $tableName
     * @return int
     * @throws ArgumentException
     */
    protected static function prepareHLBlockIdByTableName(string $tableName): int
    {
        if (empty(static::$hlblockIdsByTableName[$tableName])) {
            $row = HighloadBlockTable::getList([
                'select' => ['ID'],
                'filter' => ['=TABLE_NAME' => $tableName]
            ])->fetch();

            if (!empty($row['ID'])) {
                static::$hlblockIdsByTableName[$tableName] = (int) $row['ID'];
            }
        }

        return static::$hlblockIdsByTableName[$tableName];
    }
}
