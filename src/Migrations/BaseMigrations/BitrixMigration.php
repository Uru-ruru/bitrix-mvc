<?php

namespace Uru\BitrixMigrations\BaseMigrations;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Uru\BitrixMigrations\Exceptions\MigrationException;
use Uru\BitrixMigrations\Interfaces\MigrationInterface;

/**
 * Class BitrixMigration.
 */
class BitrixMigration implements MigrationInterface
{
    public ?bool $use_transaction = null;

    /**
     * DB connection.
     */
    protected Connection $db;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->db = Application::getConnection();
    }

    /**
     * Run the migration.
     */
    public function up(): void {}

    /**
     * Reverse the migration.
     */
    public function down(): void {}

    /**
     * Does migration use transaction.
     */
    public function useTransaction(bool $default = false): bool
    {
        if (!is_null($this->use_transaction)) {
            return $this->use_transaction;
        }

        return $default;
    }

    /**
     * Add iblock element property.
     *
     * @throws MigrationException
     */
    public function addIblockElementProperty(array $fields): int
    {
        $ibp = new \CIBlockProperty();
        $propId = $ibp->add($fields);

        if (!$propId) {
            throw new MigrationException('Ошибка при добавлении свойства инфоблока '.$ibp->LAST_ERROR);
        }

        return $propId;
    }

    /**
     * Delete iblock element property.
     *
     * @throws MigrationException
     */
    public function deleteIblockElementPropertyByCode(int|string $iblockId, string $code): void
    {
        if (!$iblockId) {
            throw new MigrationException('Не задан ID инфоблока');
        }

        if (!$code) {
            throw new MigrationException('Не задан код свойства');
        }

        $id = $this->getIblockPropIdByCode($code, $iblockId);

        \CIBlockProperty::Delete($id);
    }

    /**
     * Add User Field.
     *
     * @param mixed $fields
     *
     * @throws MigrationException
     */
    public function addUF($fields): int
    {
        if (!$fields['FIELD_NAME']) {
            throw new MigrationException('Не заполнен FIELD_NAME');
        }

        if (!$fields['ENTITY_ID']) {
            throw new MigrationException('Не заполнен код ENTITY_ID');
        }

        $oUserTypeEntity = new \CUserTypeEntity();

        $fieldId = $oUserTypeEntity->Add($fields);

        if (!$fieldId) {
            throw new MigrationException("Не удалось создать пользовательское свойство с FIELD_NAME = {$fields['FIELD_NAME']} и ENTITY_ID = {$fields['ENTITY_ID']}");
        }

        return $fieldId;
    }

    /**
     * Get UF by its code.
     *
     * @throws MigrationException
     */
    public function getUFIdByCode(string $entity, string $code): int
    {
        if (!$entity) {
            throw new MigrationException('Не задана сущность свойства');
        }

        if (!$code) {
            throw new MigrationException('Не задан код свойства');
        }

        $filter = [
            'ENTITY_ID' => $entity,
            'FIELD_NAME' => $code,
        ];

        $arField = \CUserTypeEntity::GetList(['ID' => 'ASC'], $filter)->fetch();
        if (!$arField || !$arField['ID']) {
            throw new MigrationException("Не найдено свойство с FIELD_NAME = {$filter['FIELD_NAME']} и ENTITY_ID = {$filter['ENTITY_ID']}");
        }

        return (int) $arField['ID'];
    }

    /**
     * Find iblock id by its code.
     *
     * @throws MigrationException
     */
    protected function getIblockIdByCode(string $code, ?string $iBlockType = null): int
    {
        if (!$code) {
            throw new MigrationException('Не задан код инфоблока');
        }

        $filter = [
            'CODE' => $code,
            'CHECK_PERMISSIONS' => 'N',
        ];

        if (null !== $iBlockType) {
            $filter['TYPE'] = $iBlockType;
        }

        $iblock = (new \CIBlock())->GetList([], $filter)->fetch();

        if (!$iblock['ID']) {
            throw new MigrationException("Не удалось найти инфоблок с кодом '{$code}'");
        }

        return $iblock['ID'];
    }

    /**
     * Delete iblock by its code.
     *
     * @throws MigrationException
     */
    protected function deleteIblockByCode(string $code): void
    {
        $id = $this->getIblockIdByCode($code);

        $this->db->startTransaction();
        if (!\CIBlock::Delete($id)) {
            $this->db->rollbackTransaction();

            throw new MigrationException('Ошибка при удалении инфоблока');
        }

        $this->db->commitTransaction();
    }

    /**
     * @param mixed $iblockId
     *
     * @throws MigrationException
     */
    protected function getIblockPropIdByCode(string $code, $iblockId): array|string
    {
        $filter = [
            'CODE' => $code,
            'IBLOCK_ID' => $iblockId,
        ];

        $prop = \CIBlockProperty::getList(['sort' => 'asc', 'name' => 'asc'], $filter)->getNext();
        if (!$prop || !$prop['ID']) {
            throw new MigrationException("Не удалось найти свойство с кодом '{$code}'");
        }

        return $prop['ID'];
    }
}
