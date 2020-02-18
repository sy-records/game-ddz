<?php
/**
 * User: lufei
 * Date: 2020/1/14
 * Email: lufei@swoole.com
 */

namespace App;

use Hyperf\Logger\Logger;
use Hyperf\Utils\ApplicationContext;

class Log
{
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name);
    }
}