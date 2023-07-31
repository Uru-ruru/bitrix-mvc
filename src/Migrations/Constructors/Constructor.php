<?php


namespace Uru\BitrixMigrations\Constructors;


/**
 * Class Constructor
 * @package Uru\BitrixMigrations\Constructors
 */
class Constructor
{
    /**
     * для пользователя
     */
    public const OBJ_USER = 'USER';
    /**
     * для блога
     */
    public const OBJ_BLOG_BLOG = 'BLOG_BLOG';
    /**
     * для сообщения в блоге
     */
    public const OBJ_BLOG_POST = 'BLOG_POST';
    /**
     * для комментария сообщения
     */
    public const OBJ_BLOG_COMMENT = 'BLOG_COMMENT';
    /**
     * для задач
     */
    public const OBJ_TASKS_TASK = 'TASKS_TASK';
    /**
     * для событий календаря
     */
    public const OBJ_CALENDAR_EVENT = 'CALENDAR_EVENT';
    /**
     * для попыток теста
     */
    public const OBJ_LEARN_ATTEMPT = 'LEARN_ATTEMPT';
    /**
     * для групп соцсети
     */
    public const OBJ_SONET_GROUP = 'SONET_GROUP';
    /**
     * для библиотек документов
     */
    public const OBJ_WEBDAV = 'WEBDAV';
    /**
     * для сообщений форума
     */
    public const OBJ_FORUM_MESSAGE = 'FORUM_MESSAGE';

    /**
     * для highload-блока с ID=N
     * @param $id
     * @return string
     */
    public static function objHLBlock($id): string
    {
        return "HLBLOCK_{$id}";
    }

    /**
     * для секций инфоблока с ID = N
     * @param $id
     * @return string
     */
    public static function objIBlockSection($id): string
    {
        return "IBLOCK_{$id}_SECTION";
    }

    /**
     * Для инфоблока с ID = N
     * @param $id
     * @return string
     */
    public static function objIBlock($id): string
    {
        return "IBLOCK_{$id}";
    }
}
