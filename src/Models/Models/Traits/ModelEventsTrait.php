<?php

namespace Uru\BitrixModels\Models\Traits;

trait ModelEventsTrait
{
    /**
     * Hook into before item create.
     *
     * @return mixed
     */
    protected function onBeforeCreate() {}

    /**
     * Hook into after item create.
     */
    protected function onAfterCreate(bool $result) {}

    /**
     * Hook into before item update.
     *
     * @return mixed
     */
    protected function onBeforeUpdate() {}

    /**
     * Hook into after item update.
     */
    protected function onAfterUpdate(bool $result) {}

    /**
     * Hook into before item create or update.
     *
     * @return mixed
     */
    protected function onBeforeSave() {}

    /**
     * Hook into after item create or update.
     */
    protected function onAfterSave(bool $result) {}

    /**
     * Hook into before item delete.
     *
     * @return mixed
     */
    protected function onBeforeDelete() {}

    /**
     * Hook into after item delete.
     */
    protected function onAfterDelete(bool $result) {}
}
