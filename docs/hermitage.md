
# Инструменты для работы с эрмитажем Битрикса (in development)

## Использование

Данный пакет предоставляет простое и удобное API для работы с сущностями Битрикса через эрмитаж (режим правки)

Поддерживает:
1. Добавление, изменение, удаление элементов инфоблоков (есть в коробке Битрикса, но встроенное АПИ слишком перенагружено)
2. Добавление, изменение, удаление разделов инфоблоков (есть в коробке Битрикса, но встроенное АПИ слишком перенагружено)
3. Изменение, удаление элементов хайлоад блоков (нет в коробке Битрикса)

### Пример с кнопками редактирования и удаления

```php
// Без пакета
foreach($arResult["ARTICLES"] as $article) {
    $arButtons = CIBlock::GetPanelButtons(
        $article["IBLOCK_ID"],
        $article["ID"],
        0,
        array("SECTION_BUTTONS" => false, "SESSID" => false)
    );

    $article["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
    $article["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

    $areaId = 'iblock_element_' . $advice['ID'];
    $this->AddEditAction($areaId, $article['EDIT_LINK'], CIBlock::GetArrayByID($element["IBLOCK_ID"], "ELEMENT_EDIT"));
    $this->AddDeleteAction($areaId, $article['DELETE_LINK'], CIBlock::GetArrayByID($element["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => 'Вы уверены, что хотите удалить элемент?'));

    ?><div id="<?=$this->GetEditAreaID($areaId)?>"><?= $article['NAME'] ?></div><?
}

// с пакетом
use Uru\BitrixHermitage\Action;

foreach($arResult["ARTICLES"] as $article) {
    ?><div id="<?= Action::editAndDeleteIBlockElement($this, $article) ?>"><?= $article['NAME'] ?></div><?
}
```

Аналогично с разделами инфоблоков и элементами highload блоков.

### Группы методов:

```php
Action::editAndDeleteIBlockElement($template, $element);
Action::editIBlockElement($template, $element);
Action::deleteIBlockElement($template, $element, $confirm = 'Вы уверены, что хотите удалить элемент?');

Action::editAndDeleteIBlockSection($template, $section);
Action::editIBlockSection($template, $section);
Action::deleteIBlockSection($template, $section, $confirm = 'Вы уверены, что хотите удалить раздел?');

Action::editAndDeleteHLBlockElement($template, $element);
Action::editHLBlockElement($template, $element);
Action::deleteHLBlockElement($template, $element, $confirm = 'Вы уверены, что хотите удалить элемент?');
```
Все эти методы возвращают строку которую надо вставить в id нужного html тэга.

### Пример с кнопками добавления

Кнопки добавления в эрмитаже реализуются по-другому.
Им не нужно указывать конкретный html блок, вместо этого они цепляются ко всему компоненту сразу.

```php
// Без пакета
if($APPLICATION->GetShowIncludeAreas()) {
    $arButtons = CIBlock::GetPanelButtons($iblockId, 0, 0, [...]);
    $this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
}

// С пакетом
\Uru\BitrixHermitage\Action::addForIBlock($this, $iblockId, [...]);
// В отличии от варианта выше, данный метод можно вызывать как в компоненте, так и в шаблоне. Он понимает и то, и другое в качестве первого параметра.
```

Массив опций `[...]` полностью соответствует массиву `$arOptions` метода [CIBlock::GetPanelButtons](https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblock/getpanelbuttons.php)
Например, передав в него `'SECTION_BUTTONS' => false` можно отключить показ кнопки добавления раздела, а передав `'CATALOG'=>true` включить работу с модулем каталога.
В простейшем случае этот параметр можно и вовсе опустить.

### Что такое `$element` и `$section`?
`$element` и `$section` это массивы (либо объекты реализующие интерфейс `ArrayAccess`)
Для инфоблоков они должны содержать `ID` и `IBLOCK_ID`.
Для хайлоадблокрв они должны содержать `ID` и `HLBLOCK_ID`/`HLBLOCK_TABLE_NAME`.
Также для инфоблоков в качестве `$element` и `$section` можно передать просто ID элемента или раздела инфоблока. В этом случае будут доп запросы в БД, но выполнены они будут только в режиме правки.

> Замечание
Если вы для хайлоадблоков используете `eloquent` из `arrilot/bitrix-models`, то стоит добавить следующий метод в модель:

```php
public function getHlblockIdAttribute()
{
    return 1; // поменять на нужный идентификатор
}
```


#Директивы по работе с эрмитажем

Таблица соответствия директив и методов пакета

```php
@actionEditAndDeleteIBlockElement($element)             => Action::editAndDeleteIBlockElement($template, $element),
@actionEditIBlockElement($element)                      => Action::editIBlockElement($template, $element),
@actionDeleteIBlockElement($element, $confirm = '...')  => Action::deleteIBlockElement($template, $element, $confirm = '...'),

@actionEditAndDeleteIBlockSection($section)             => Action::editAndDeleteIBlockSection($template, $section),
@actionEditIBlockSection($section)                      => Action::editIBlockSection($template, $section),
@actionDeleteIBlockSection($section, $confirm = '...')  => Action::deleteIBlockSection($template, $section, $confirm = '...'),

@actionEditAndDeleteHLBlockElement($element)            => Action::editAndDeleteHLBlockElement($template, $element) ,
@actionEditHLBlockElement($element)                     => Action::editHLBlockElement($template, $element),
@actionDeleteHLBlockElement($element, $confirm = '...') => Action::deleteHLBlockElement($template, $element, $confirm = '...'),

@actionAddForIBlock($iblockId, [...])                   => Action::addForIBlock($templateOrComponent, $iblockId, [...]),
```
