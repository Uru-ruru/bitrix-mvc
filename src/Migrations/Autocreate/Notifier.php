<?php

namespace Uru\BitrixMigrations\Autocreate;

class Notifier
{
    /**
     * Notification tag.
     */
    protected string $tag = 'uru_new_migration';

    /**
     * Show notification that migration has been created.
     *
     * @param mixed $migration
     */
    public function newMigration($migration)
    {
        $notification = [
            'MESSAGE' => 'Migration <strong>'.$migration.'</strong> has been created and applied.',
            'TAG' => $this->tag,
            'MODULE_ID' => 'main',
            'ENABLE_CLOSE' => 'Y',
        ];

        \CAdminNotify::add($notification);
    }

    /**
     * Delete notification from the previous migration.
     */
    public function deleteNotificationFromPreviousMigration(): void
    {
        if (defined('ADMIN_SECTION')) {
            \CAdminNotify::deleteByTag($this->tag);
        }
    }
}
