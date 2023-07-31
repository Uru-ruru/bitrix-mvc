<?php

namespace Uru\Tests\BitrixModels;

use Uru\Tests\BitrixModels\Stubs\TestD7Element;
use Uru\Tests\BitrixModels\Stubs\TestD7Element2;
use Uru\Tests\BitrixModels\Stubs\TestD7ResultObject;
use Mockery as m;
use Uru\BitrixModels\Adapters\D7Adapter;

class D7ModelTest extends ModelTestCase
{
    public function testInitialization(): void
    {
        $element = new TestD7Element(1);
        $this->assertSame(1, $element->id);

        $fields = [
            'UF_EMAIL' => 'John',
            'UF_IMAGE_ID' => '1',
        ];
        $element = new TestD7Element(1, $fields);
        $this->assertSame(1, $element->id);
        $this->assertSame($fields, $element->fields);
    }

    public function testMultipleInitialization(): void
    {
        // 1
        $element = new TestD7Element(1);
        $this->assertSame(1, $element->id);

        $fields = [
            'UF_EMAIL' => 'John',
            'UF_IMAGE_ID' => '1',
        ];
        $element = new TestD7Element(1, $fields);
        $this->assertSame(1, $element->id);
        $this->assertSame($fields, $element->fields);

        // 2
        $element2 = new TestD7Element2(1);
        $this->assertSame(1, $element2->id);

        $fields = [
            'UF_EMAIL' => 'John',
            'UF_IMAGE_ID' => '1',
        ];
        $element2 = new TestD7Element2(1, $fields);
        $this->assertSame(1, $element2->id);
        $this->assertSame($fields, $element2->fields);

//        dd([TestD7Element::cachedTableClass(), TestD7Element2::cachedTableClass()]);
        $this->assertNotSame(TestD7Element::cachedTableClass(), TestD7Element2::cachedTableClass());
        $this->assertNotSame(TestD7Element::instantiateAdapter(), TestD7Element2::instantiateAdapter());
    }

    public function testAdd(): void
    {
        $resultObject = new TestD7ResultObject();
        $adapter = m::mock(D7Adapter::class);
        $adapter->expects('add')->with(['UF_NAME' => 'Jane', 'UF_AGE' => '18'])->andReturns($resultObject);

        TestD7Element::setAdapter($adapter);
        $element = TestD7Element::create(['UF_NAME' => 'Jane', 'UF_AGE' => '18']);
        $this->assertEquals(1, $element->id);
        $this->assertEquals(['UF_NAME' => 'Jane', 'UF_AGE' => '18', 'ID' => '1'], $element->fields);
    }

    public function testUpdate()
    {
        $resultObject = new TestD7ResultObject();
        $adapter = m::mock(D7Adapter::class);
        $adapter->expects('update')->with(1, ['UF_NAME' => 'Jane'])->andReturns($resultObject);

        $element = new TestD7Element(1);
        TestD7Element::setAdapter($adapter);


        $this->assertTrue($element->update(['UF_NAME' => 'Jane']));
    }

    public function testDelete()
    {
        // normal
        $resultObject = new TestD7ResultObject();
        $adapter = m::mock(D7Adapter::class);
        $adapter->shouldReceive('delete')->once()->with(1)->andReturn($resultObject);

        $element = m::mock('Uru\Tests\BitrixModels\Stubs\TestD7Element[onAfterDelete, onBeforeDelete]', [1])
            ->shouldAllowMockingProtectedMethods();
        $element::setAdapter($adapter);
        $element->shouldReceive('onBeforeDelete')->once()->andReturn(null);
        $element->shouldReceive('onAfterDelete')->once()->with(true);

        $this->assertTrue($element->delete());

        // cancelled
        $element = m::mock('Uru\Tests\BitrixModels\Stubs\TestD7Element[onAfterDelete, onBeforeDelete]', [1])
            ->shouldAllowMockingProtectedMethods();
        $element->shouldReceive('onBeforeDelete')->once()->andReturn(false);
        $element->shouldReceive('onAfterDelete')->never();
        $this->assertFalse($element->delete());
    }
}
