<?php

namespace Uru\BitrixMigrations;

/**
 * Class Logger
 * @package Uru\BitrixMigrations
 */
class Logger
{
    /**
     *
     */
    public const COLOR_BLACK = '0;30';
    /**
     *
     */
    public const COLOR_DARK_GRAY = '1;30';
    /**
     *
     */
    public const COLOR_BLUE = '0;34';
    /**
     *
     */
    public const COLOR_LIGHT_BLUE = '1;34';
    /**
     *
     */
    public const COLOR_GREEN = '0;32';
    /**
     *
     */
    public const COLOR_LIGHT_GREEN = '1;32';
    /**
     *
     */
    public const COLOR_CYAN = '0;36';
    /**
     *
     */
    public const COLOR_LIGHT_CYAN = '1;36';
    /**
     *
     */
    public const COLOR_RED = '0;31';
    /**
     *
     */
    public const COLOR_LIGHT_RED = '1;31';
    /**
     *
     */
    public const COLOR_PURPLE = '0;35';
    /**
     *
     */
    public const COLOR_LIGHT_PURPLE = '1;35';
    /**
     *
     */
    public const COLOR_BROWN = '0;33';
    /**
     *
     */
    public const COLOR_YELLOW = '1;33';
    /**
     *
     */
    public const COLOR_LIGHT_GRAY = '0;37';
    /**
     *
     */
    public const COLOR_WHITE = '1;37';

    /**
     * @param $string
     * @param null $foreground_color
     */
    public static function log($string, $foreground_color = null): void
    {
        $colored_string = "";

        if ($foreground_color) {
            $colored_string .= "\033[" . $foreground_color . "m";
        }

        $colored_string .= $string . "\033[0m\n";

        fwrite(STDOUT, $colored_string);
    }
}
