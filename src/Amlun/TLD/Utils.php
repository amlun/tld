<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/3/24
 * Time: 下午3:19
 */
namespace Amlun\TLD;

class Utils
{
    /**
     * @param $search
     * @param $string
     * @return bool
     */
    public static function start_with($search, $string)
    {
        return (substr($search, 0, strlen($string)) == $string);
    }

    /**
     * @param $search
     * @param $string
     * @return bool
     */
    public static function end_with($search, $string)
    {
        return (substr($search, -strlen($string)) == $string);
    }
}