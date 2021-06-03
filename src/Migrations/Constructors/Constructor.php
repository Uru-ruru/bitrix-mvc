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
    const OBJ_USER = 'USER';
    /**
     * для блога
     */
    const OBJ_BLOG_BLOG = 'BLOG_BLOG';
    /**
     * для сообщения в блоге
     */
    const OBJ_BLOG_POST = 'BLOG_POST';
    /**
     * для комментария сообщения
     */
    const OBJ_BLOG_COMMENT = 'BLOG_COMMENT';
    /**
     * для задач
     */
    const OBJ_TASKS_TASK = 'TASKS_TASK';
    /**
     * для событий календаря
     */
    const OBJ_CALENDAR_EVENT = 'CALENDAR_EVENT';
    /**
     * для попыток теста
     */
    const OBJ_LEARN_ATTEMPT = 'LEARN_ATTEMPT';
    /**
     * для групп соцсети
     */
    const OBJ_SONET_GROUP = 'SONET_GROUP';
    /**
     * для библиотек документов
     */
    const OBJ_WEBDAV = 'WEBDAV';
    /**
     * для сообщений форума
     */
    const OBJ_FORUM_MESSAGE = 'FORUM_MESSAGE';

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
