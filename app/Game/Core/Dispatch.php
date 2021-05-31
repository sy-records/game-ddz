<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Game\Core;

use App\Game\Conf\Route;

/**
 * 调度运行游戏逻辑策略,分别调度到不同协议目录里，策略模式容器.
 */
class Dispatch
{
    /**
     * 策略对象
     * @var object
     */
    private $_strategy;

    /**
     * 参数配置文件.
     * @var array
     */
    private $_params = [];

    /**
     * 构造你要使用的策略
     * Dispatch constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->_params = $params;
        $this->Strategy();
    }

    /**
     * 逻辑处理策略路由转发, 游戏逻辑策略转发， 根据主命令字和子命令字来转发.
     */
    public function Strategy()
    {
        //获取路由策略
        $route = Route::$cmd_map;
        if (isset($this->_params['cmd'], $this->_params['scmd'])) {
            //获取策略类名
            $classname = $route[$this->_params['cmd']][$this->_params['scmd']] ?? '';
            //转发到对应目录处理逻辑
            $classname = 'App\Game\Logic\\' . $classname;
            if (class_exists($classname)) {
                $this->_strategy = new $classname($this->_params);
                Log::show("Class: {$classname}");
            } else {
                Log::show("Websockt Error: class is not support,cmd is {$this->_params['cmd']},scmd is {$this->_params['scmd']}");
            }
        }
    }

    /**
     * 获取策略.
     */
    public function getStrategy()
    {
        return $this->_strategy;
    }

    /**
     * 执行策略.
     */
    public function exec()
    {
        return $this->_strategy->exec();
    }
}
