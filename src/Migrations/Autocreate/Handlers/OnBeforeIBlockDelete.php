<?php

namespace Uru\BitrixMigrations\Autocreate\Handlers;

class OnBeforeIBlockDelete extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->fields = $this->getIBlockById($params[0]);
    }

    /**
     * Get migration name.
     */
    public function getName(): string
    {
        return "auto_delete_iblock_{$this->fields['CODE']}";
    }

    /**
     * Get template name.
     */
    public function getTemplate(): string
    {
        return 'auto_delete_iblock';
    }

    /**
     * Get array of placeholders to replace.
     */
    public function getReplace(): array
    {
        return [
            'code' => "'".$this->fields['CODE']."'",
        ];
    }

    /**
     * Get iblock by id without checking permissions.
     *
     * @param mixed $id
     */
    protected function getIBlockById($id): array
    {
        $filter = [
            'ID' => $id,
            'CHECK_PERMISSIONS' => 'N',
        ];

        return (new \CIBlock())->getList([], $filter)->fetch();
    }
}
