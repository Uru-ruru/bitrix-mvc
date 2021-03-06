# PHP Collectors (In development)

## Introduction

Collectors scan across given fields in items/collections for ids and fetch detailed data from database or another storage

## Usage

First of all you need to create your own collector class.

```php

use Uru\BitrixCollectors\Collector;

class FooCollector extends Collector
{
    /**
     * Get data for given ids.
     *
     * @param array $ids
     * @return array
     */
    public function getList(array $ids)
    {
        ...
    }
}
```

Example
```php
    $elements = [
        ['id' => 1, 'files' => 1],
        ['id' => 2, 'files' => [2, 1]],
    ];
    
    $item = [
        'id' => 3,
        'another_files' => 3
    ];
    
    $collector = new FooCollector();
    $collector->scanCollection($elements, 'files');
    $collector->scanItem($item, 'another_files'); 
    // You can also pass several fields as array  - $collector->scanItem($item, ['field_1', 'field_2']);
    
    $files = $collector->performQuery();
    var_dump($files);

    // result
    /*
        array:2 [▼
          1 => array:3 [▼
              "id" => 1
              "name" => "avatar.png",
              "module" => "main",
          ]
          2 => array:3 [▼
              "id" => 2
              "name" => "test.png",
              "module" => "main",
          ],
          3 => array:3 [▼
               "id" => 3
               "name" => "test2.png",
               "module" => "main",
          ],
        ]
    */
```

You can manually add additional ids if you already know them.
```php
$files = $collector->addIds([626, 277, 23])->performQuery();
```

You can pass `select` to `getlist` like that:
```php
$files = $collector->select(['id', 'name'])->performQuery();
// $this->select is ['id', 'name'] in `->getList()` and you can implement logic handling it.
```

Same is true for an additional filter.
```php
$collector->where(['active' => 1])->performQuery();
// $this->where is ['active' => 1]
```

You can use dot notation to locate a field, e.g
```php
$collector->fromItem($item, 'properties.files');
```

# Мост для интеграции `collectors` с Битриксом (В разработке)

## Использование

Пакет позволяет собрать из различных коллекций и элементов (обычно полученных через какой-нибудь `CIblockElement::GetList()`) идентификаторы и удобным образом дополучить по ним дополнительные данные *одним запросом*, а не в цикле как это обычно заканчивается

Данный мост реализует несколько наиболее востребованных в Битриксе коллекторов (collectors)

Готовые коллекторы:
 1. `Uru\BitrixCollectors\FileCollector` - импользует внутри себя FileTable::getList из d7
 2. `Uru\BitrixCollectors\SectionCollector` - SectionTable::getList
 3. `Uru\BitrixCollectors\ElementCollector` - CIBlockElement::GetList + Fetch. Рекомендуется использовать инфоблоки 2.0, чтобы не было проблем с множественными свойствами.
 4. `Uru\BitrixCollectors\UserCollector` - UserTable::getList

Абстрактные классы-коллекторы. От них можно наследоваться при разработке дополнительных танкеров.
 1. `Uru\BitrixCollectors\TableCollector` - для случая когда данные хранятся в отдельной таблице и для неё НЕТ d7 orm класса. 
 2. `Uru\BitrixCollectors\OrmTableCollector` - для случая когда данные хранятся в отдельной таблице и ЕСТЬ d7 orm класс. 

Также как и с оригинальным пакетом цепочка методов должна заканчиваться методом `performQuery()` который выполняем getList запрос в БД и возвращает результат. Можно одновременно собирать идентификаторы по нескольким коллекциям/элементам и т д.

Пример:
```php
    use Uru\BitrixCollectors\FileCollector;

    $items = [
        ['ID' => 1, 'PROPERTY_FILES1_VALUE' => 1],
        ['ID' => 2, 'PROPERTY_FILES2_VALUE' => [2, 1]],
    ];
    
    $item = ['ID' => 3, 'PROPERTY_OTHER_FILES_VALUE' => 4];
    
    $collector = new FileCollector();
    $files = $collector->scanCollection($items, ['PROPERTY_FILES1_VALUE', 'PROPERTY_FILES2_VALUE'])
                       ->scanItem($item, 'PROPERTY_OTHER_FILES_VALUE')
                       ->performQuery();
    var_dump($files);

    // результат
    /*
        array:3 [▼
            1 => array:13 [▼
              "ID" => "1"
              "TIMESTAMP_X" => "2017-02-10 17:25:17"
              "MODULE_ID" => "iblock"
              "HEIGHT" => "150"
              "WIDTH" => "140"
              "FILE_SIZE" => "15003"
              "CONTENT_TYPE" => "image/png"
              "SUBDIR" => "iblock/b03"
              "FILE_NAME" => "avatar.png"
              "ORIGINAL_NAME" => "avatar-gs.png"
              "DESCRIPTION" => ""
              "HANDLER_ID" => null
              "EXTERNAL_ID" => "125dc3213f7ecde31124f3ebca7322b5"
           ],
           2 => array:13 [▼
              "ID" => "2"
              "TIMESTAMP_X" => "2017-02-10 17:31:30"
              "MODULE_ID" => "iblock"
              "HEIGHT" => "84"
              "WIDTH" => "460"
              "FILE_SIZE" => "4564"
              "CONTENT_TYPE" => "image/png"
              "SUBDIR" => "iblock/fcf"
              "FILE_NAME" => "4881-03.png"
              "ORIGINAL_NAME" => "4881-03.png"
              "DESCRIPTION" => ""
              "HANDLER_ID" => null
              "EXTERNAL_ID" => "35906df62694b4ed5f150c468a1f5d72"
           ]
           4 => array:13 [▼
              "ID" => "4"
              "TIMESTAMP_X" => "2017-02-10 17:33:30"
              "MODULE_ID" => "iblock"
              "HEIGHT" => "84"
              "WIDTH" => "460"
              "FILE_SIZE" => "4564"
              "CONTENT_TYPE" => "image/png"
              "SUBDIR" => "iblock/fc2"
              "FILE_NAME" => "test.png"
              "ORIGINAL_NAME" => "4881-03.png"
              "DESCRIPTION" => ""
              "HANDLER_ID" => null
              "EXTERNAL_ID" => "35906df62694b4ed5f150c468a1f5d53"
           ]
        ]
    */
```

Все коллекторы поддерживают `->select([...])`, в котором можно указать массив `$select`, который передается в API битрикса.
Аналогично в `->where(['...'])` можно указать `$filter`
Исключение - `TableCollector`. Там в `->where()` нужно передавать строку, а не массив-фильтр.
Она будет подставлена в sql запрос без дополнительный обработки.
