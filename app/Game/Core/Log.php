<?php
namespace App\Game\Core;

class Log
{
    /**
     * 日志登录类型
     * @var array
     */
    protected static $level_info = array(
        1 => 'INFO',
        2 => 'DEBUG',
        3=>'ERROR'
    );

    /**
     * 日志等级，1表示大于等于1的等级的日志，都会显示，依次类推
     * @var int
     */
    protected static $level = 1;

    /**
     *  显示日志
     * @param string $centent
     * @param int $level
     */
    public static function show($centent = '', $level = 1, $str = '')
    {
        if($level >= self::$level) {
            echo $str.date('Y/m/d H:i:s') . " [\033[0;36m" . self::$level_info[$level] . "\033[0m]  " . $centent . "\n";
        }
    }

    /**
     *  显示日志
     * @param string $centent
     * @param int $level
     */
    public static function split($split = '', $level = 1)
    {
        if($level >= self::$level) {
            echo $split . "\n";
        }
    }
}




