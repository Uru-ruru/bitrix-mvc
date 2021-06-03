<?php

use Uru\BitrixModels\Models\D7Model;

class BaseD7Model extends D7Model
{
    /**
     * Проверить идентификатор
     * @return int
     */
    public function getId(): int
    {
        return $this['ID'];
    }
}
