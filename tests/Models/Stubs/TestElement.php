<?php

namespace Uru\Tests\BitrixModels\Stubs;

use Uru\BitrixModels\Models\ElementModel;
use Illuminate\Support\Collection;

/**
 * Class TestElement
 * @package Uru\Tests\BitrixModels\Stubs
 *
 * @property Collection|TestElement2[] $elements
 * @property TestElement2 $element
 */
class TestElement extends ElementModel
{
    protected array $appends = ['ACCESSOR_THREE', 'PROPERTY_LANG_ACCESSOR_ONE'];

    protected array $languageAccessors = ['PROPERTY_LANG_ACCESSOR_ONE'];

    const IBLOCK_ID = 1;

    public function getAccessorOneAttribute($value): string
    {
        return '!'.$value.'!';
    }

    public function getAccessorTwoAttribute(): string
    {
        return $this['ID'].':'.$this['NAME'];
    }

    public function getAccessorThreeAttribute(): array
    {
        return [];
    }

    public function scopeStopActionScope($query): bool
    {
        return false;
    }

    public function elements()
    {
        return $this->hasMany(TestElement2::class, 'ID', 'PROPERTY_ELEMENT_VALUE');
    }

    public function element()
    {
        return $this->hasOne(TestElement2::class, 'PROPERTY_ELEMENT_VALUE', 'ID');
    }
}
