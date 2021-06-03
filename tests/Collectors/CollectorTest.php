<?php

namespace Collectors;

use Uru\Tests\Collectors\Stubs\FooArrayAccessClass;
use Uru\Tests\Collectors\Stubs\FooCollector;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class CollectorTest extends TestCase
{
    public function test_it_can_collect_from_a_basic_collection()
    {
        $collector = new FooCollector();
        $collection = [
          [
              'file' => 2,
          ],
          [
              'file' => 1,
          ],
        ];

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            1 => [
                'id'  => 1,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_can_collect_from_a_collection_with_empty_or_null_field()
    {
        $collector = new FooCollector();
        $collection = [
            [
                'file' => 2,
            ],
            [
                'file' => '',
            ],
            [
                'file' => null,
            ],
        ];

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_can_collect_from_illuminate_collection()
    {
        $collector = new FooCollector();
        $collection = new Collection([
            [
                'file' => 2,
            ],
            [
                'file' => 1,
            ],
        ]);

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            1 => [
                'id'  => 1,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_can_collect_from_a_collection_with_multivalue_fields()
    {
        $collector = new FooCollector();
        $collection = [
            [
                'file' => 2,
            ],
            [
                'file' => [3, 4],
            ],
        ];

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            3 => [
                'id'  => 3,
                'foo' => 'bar',
            ],
            4 => [
                'id'  => 4,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected,  $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_can_collect_from_a_collection_with_multivalue_fields_as_illuminate_collection()
    {
        $collector = new FooCollector();
        $collection = [
            [
                'file' => new Collection([3, 4]),
            ],
            [
                'file' => 2,
            ],
        ];

        $expected = [
            3 => [
                'id'  => 3,
                'foo' => 'bar',
            ],
            4 => [
                'id'  => 4,
                'foo' => 'bar',
            ],
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected,  $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_can_collect_from_a_collection_with_multiple_fields_passed_as_an_array()
    {
        $collector = new FooCollector();
        $collection = [
            [
                'file'  => 2,
                'file2' => 3,
            ],
            [
                'file'  => [3, 4],
                'file2' => [1, ''],
            ],
        ];

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            3 => [
                'id'  => 3,
                'foo' => 'bar',
            ],
            4 => [
                'id'  => 4,
                'foo' => 'bar',
            ],
            1 => [
                'id'  => 1,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanCollection($collection, ['file', 'file2'])->performQuery());
    }

    public function test_it_can_collect_from_a_single_item()
    {
        $collector = new FooCollector();
        $item = [
            'file' => 2,
        ];

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanItem($item, 'file')->performQuery());
    }

    public function test_it_allows_to_manually_add_ids()
    {
        $collector = new FooCollector();

        $expected = [
            3 => [
                'id'  => 3,
                'foo' => 'bar',
            ],
            4 => [
                'id'  => 4,
                'foo' => 'bar',
            ],
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->addIds([3, 4, 2])->performQuery());
    }

    public function test_it_can_collect_from_a_array_access_object()
    {
        $collector = new FooCollector();
        $item = new FooArrayAccessClass([
            'file' => 2,
        ]);

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanItem($item, 'file')->performQuery());
    }

    public function test_it_can_collect_from_a_collection_of_array_access_objects()
    {
        $collector = new FooCollector();

        $collection = [
            new FooArrayAccessClass([
                'id' => 1,
                'file'  => 2,
            ]),
            new FooArrayAccessClass([
                'id' => 2,
                'file'  => [3, 4],
            ]),
        ];

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            3 => [
                'id'  => 3,
                'foo' => 'bar',
            ],
            4 => [
                'id'  => 4,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_can_collect_from_a_single_item_and_a_collection_at_the_same_time()
    {
        $collector = new FooCollector();

        $item = [
            'file' => 2,
        ];

        $collection = [
            [
                'file' => 2,
            ],
            [
                'file' => [1, 3]
            ],
        ];

        $collector->scanItem($item, 'file');
        $collector->scanCollection($collection, 'file');

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            1 => [
                'id'  => 1,
                'foo' => 'bar',
            ],
            3 => [
                'id'  => 3,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->performQuery());
    }

    public function test_it_can_collect_from_a_collection_according_to_select()
    {
        $collector = new FooCollector();
        $collection = [
            [
                'file' => 2,
            ],
            [
                'file' => 1,
            ],
        ];

        $expected = [
            2 => [
                'foo' => 'bar',
            ],
            1 => [
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->scanCollection($collection, 'file')->select(['foo'])->performQuery());
    }

    public function test_it_can_return_if_no_ids_are_found()
    {
        $collector = new FooCollector();
        $collection = [
            [
                'file' => '',
            ],
            [
                'file' => [],
            ],
        ];

        $this->assertEquals([], $collector->scanCollection($collection, 'file')->performQuery());
    }

    public function test_it_does_not_raise_undefined_index_if_the_field_is_not_present()
    {
        $collector = new FooCollector();

        $item = [
            'id' => 3,
        ];

        $collector->scanItem($item, 'file');

        $this->assertEquals([], $collector->performQuery());
    }

    public function test_it_can_collect_data_using_dot_notation()
    {
        $collector = new FooCollector();

        $item = [
            'id' => 3,
            'element' => [
                'id' => 1,
                'file' => [2, 1]
            ],
        ];

        $collector->scanItem($item, 'element.file');

        $expected = [
            2 => [
                'id'  => 2,
                'foo' => 'bar',
            ],
            1 => [
                'id'  => 1,
                'foo' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $collector->performQuery());
    }
}
