<?php

use Uru\BitrixModels\Models\D7Model;

class BaseD7Model extends D7Model
{
    /**
     * Проверить идентификатор
     */
    public function getId(): int
    {
        return $this['ID'];
    }
}
