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

/**
 * Joker poker 算法逻辑.
 * @author：jiagnxinyu
 */
class JokerPoker
{
    /**
     * 扑克牌值列表.
     * @var array
     */
    public static $card_value_list = [
        1 => 'A', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => '10', 11 => 'J', 12 => 'Q', 13 => 'K',
        17 => 'A', 18 => '2', 19 => '3', 20 => '4', 21 => '5', 22 => '6', 23 => '7', 24 => '8', 25 => '9', 26 => '10', 27 => 'J', 28 => 'Q', 29 => 'K',
        33 => 'A', 34 => '2', 35 => '3', 36 => '4', 37 => '5', 38 => '6', 39 => '7', 40 => '8', 41 => '9', 42 => '10', 43 => 'J', 44 => 'Q', 45 => 'K',
        49 => 'A', 50 => '2', 51 => '3', 52 => '4', 53 => '5', 54 => '6', 55 => '7', 56 => '8', 57 => '9', 58 => '10', 59 => 'J', 60 => 'Q', 61 => 'K',
        79 => 'JOKER',
    ];

    /**
     * 赖子的key值，和牌的key值对应.
     * @var int
     */
    public static $laizi_value = 79;

    /**
     * 花色.
     */
    public static $card_color = [
        0 => '方块',
        1 => '黑桃',
        2 => '红桃',
        3 => '梅花',
    ];

    /**
     * 牌型.
     * @var array
     */
    public static $card_type = [
        0 => '非赢牌',
        1 => '对K或者以上',
        2 => '两对',
        3 => '三条',
        4 => '顺子',
        5 => '同花',
        6 => '葫芦',
        7 => '四条',
        8 => '同花顺',
        9 => '五条',
        10 => '带赖子皇家同花顺',
        11 => '皇家同花顺',
    ];

    /**
     * 牌型赔付的倍率.
     * @var array
     */
    public static $card_rate = [
        0 => 0,
        1 => 1,
        2 => 1,
        3 => 2,
        4 => 3,
        5 => 5,
        6 => 7,
        7 => 17,
        8 => 50,
        9 => 100,
        10 => 200,
        11 => 250,
    ];

    /**
     * 是否翻倍的概率配置：1表示不翻倍回收奖励，2.表示再来一次 3,表示奖励翻倍.
     */
    public static $is_double_rate = [
        1 => 5000,
        2 => 1000,
        3 => 4000,
    ];

    /**
     * 是否翻倍提示语.
     */
    public static $is_double_msg = [
        1 => '不翻倍回收奖励',
        2 => '再来一次,不回收奖励',
        3 => '奖励翻倍',
    ];

    /**
     * 是否有赖子牌， 如果有赖子牌，这个值就是true， 默认false.
     */
    public static $is_laizi = false;

    /**
     * 是否为顺子，是true，否false.
     */
    public static $is_shunzi = false;

    /**
     * 是否为最大顺子，是true，否false.
     */
    public static $is_big_shunzi = false;

    /**
     * 是否为同花，是true，否false.
     */
    public static $is_tonghua = false;

    /**
     * 随机获取5张牌，如果参数指定n张牌， 就补齐5-n张牌.
     * @param mixed $arr
     */
    public static function getFiveCard($arr = [])
    {
        $card = self::$card_value_list;
        $num = 5 - count($arr);
        if ($num == 0) {
            $card_key = $arr;
        } else {
            //去除上面的牌， 防止重复出现
            foreach ($arr as $v) {
                unset($card[$v]);
            }
            $card_key = array_rand($card, $num);
            if (! is_array($card_key)) {
                $card_key = [$card_key];
            }
            $card_key = array_merge($card_key, $arr);
        }
        return $card_key;
    }

    /**
     * 随机获取1张牌,不包括王.
     */
    public static function getOneCard()
    {
        $card = self::$card_value_list;
        unset($card[79]);
        $card_key = array_rand($card, 1);
        if (! is_array($card_key)) {
            $card_key = [$card_key];
        }
        return $card_key;
    }

    /**
     * 获取牌内容，并显示花色， 方便直观查看.
     * @param mixed $arr
     */
    public static function showCard($arr)
    {
        $show = [];
        $card = self::getCard($arr);
        foreach ($card as $k => $v) {
            if ($k != self::$laizi_value) {
                $key = floor($k / 16);
                $show[] = self::$card_color[$key] . '_' . $v;
            } else {
                $show[] = $v;
            }
        }
        return implode(',', $show);
    }

    /**
     * 不带赖子皇家同花顺.
     */
    public static function isBigTongHuaShun()
    {
        return (self::$is_tonghua && self::$is_shunzi && self::$is_big_shunzi && ! self::$is_laizi) ? true : false;
    }

    /**
     * 带来赖子皇家同花顺.
     */
    public static function isBigTongHuaShunByLaizi()
    {
        return (self::$is_tonghua && self::$is_shunzi && self::$is_big_shunzi && self::$is_laizi) ? true : false;
    }

    /**
     * 是否为同花顺.
     */
    public static function isTongHuaShun()
    {
        return (self::$is_tonghua && self::$is_shunzi) ? true : false;
    }

    /**
     * 是否为同花牌，判断同花的算法.
     * @param mixed $arr
     */
    public static function isTongHua($arr)
    {
        $sub = [];
        foreach ($arr as $v) {
            $sub[] = floor($v / 16);
        }
        $u = array_unique($sub);
        if (count($u) == 1) {
            self::$is_tonghua = true;
        } else {
            self::$is_tonghua = false;
        }
        return self::$is_tonghua;
    }

    /**
     * 是否为顺子牌，判断顺子的算法.
     * @param mixed $arr
     */
    public static function isShunZi($arr)
    {
        $flag = 0;
        $card = self::getCard($arr);
        asort($card);
        $min = key($card) % 16;
        if ($min >= 2 && $min <= 10) {
            //最小或者最大顺子，需要特殊处理
            /* if(($min == 2 || $min == 10) && array_search('A', $card) !== false) {
                $flag++;
            } */
            if (array_search('A', $card) !== false) {
                if ($min == 2) {
                    $min = 1;
                } elseif ($min == 10) {
                    ++$flag;
                }
            }
            $cnt = count($arr);
            for ($i = 1; $i < 5; ++$i) {
                $next = $min + $i;
                if (in_array($next, $arr) || in_array(($next + 16), $arr) || in_array(($next + 32), $arr) || in_array(($next + 48), $arr)) {
                    ++$flag;
                }
            }
        }
        if ($flag == $cnt - 1) {
            self::$is_shunzi = true;
        } else {
            self::$is_shunzi = false;
        }
        //是否为最大顺子，是true，否false
        if ($min == 10) {
            self::$is_big_shunzi = true;
        } else {
            self::$is_big_shunzi = false;
        }
        return self::$is_shunzi;
    }

    /**
     * 取模值,算对子，两对，三张，四条，5条的算法.
     * @param mixed $arr
     */
    public static function _getModValue($arr)
    {
        $flag = $type = 0;
        $mod = [];
        foreach ($arr as $k => $v) {
            $mod[] = $v % 16;
        }
        $v = array_count_values($mod);
        $cnt = count($v);
        if (self::$is_laizi) {
            if (in_array(1, $v) && $cnt == 4) {
                //对子
                $card = self::getCard($arr);
                if (array_search('A', $card) !== false || array_search('K', $card) !== false) {
                    $type = 1; //对K或更大
                }
            } elseif (in_array(2, $v) && $cnt == 3) {
                $type = 3; //三张
            } elseif (in_array(2, $v) && $cnt == 2) {
                $type = 4; //葫芦
            } elseif (in_array(3, $v)) {
                $type = 5; //四条
            } elseif (in_array(4, $v)) {
                $type = 6; //五条
            }
        } else {
            if (in_array(2, $v) && $cnt == 4) {
                //对子
                $card = self::getCard($arr);
                $card_key = array_count_values($card);
                arsort($card_key);
                $kw = key($card_key);
                if ($kw == 'A' || $kw == 'K') {
                    $type = 1; //对K或更大
                }
            } elseif (in_array(2, $v) && $cnt == 3) {
                $type = 2; //两对
            } elseif (in_array(3, $v) && $cnt == 3) {
                $type = 3; //三张
            } elseif (in_array(3, $v) && $cnt == 2) {
                $type = 4; //葫芦
            } elseif (in_array(4, $v)) {
                $type = 5; //四条
            }
        }
        return $type;
    }

    /**
     * 五张.
     * @param mixed $type
     */
    public static function isWuZhang($type)
    {
        return $type == 6 ? true : false;
    }

    /**
     * 四张.
     * @param mixed $type
     */
    public static function isSiZhang($type)
    {
        return $type == 5 ? true : false;
    }

    /**
     * 葫芦.
     * @param mixed $type
     */
    public static function isHulu($type)
    {
        return $type == 4 ? true : false;
    }

    /**
     * 三张.
     * @param mixed $type
     */
    public static function isSanZhang($type)
    {
        return $type == 3 ? true : false;
    }

    /**
     * 两对.
     * @param mixed $type
     */
    public static function isLiangDui($type)
    {
        return $type == 2 ? true : false;
    }

    /**
     * 大于对K或更大.
     * @param mixed $type
     */
    public static function isDaYuQDui($type)
    {
        return $type == 1 ? true : false;
    }

    /**
     * 检查牌型，判断用户所翻的牌为那种牌型.
     * @param mixed $arr
     */
    public static function checkCardType($arr)
    {
        //去除赖子牌
        $arr_card = self::exceptLaizi($arr);
        $type = self::_getModValue($arr_card);
        if (self::isWuZhang($type)) {
            return 9;   //五条
        }
        if (self::isSiZhang($type)) {
            return 7;   //四条
        }
        if (self::isHulu($type)) {
            return 6;   //葫芦，三张两对
        }
        if (self::isSanZhang($type)) {
            return 3;   //三张
        }
        if (self::isLiangDui($type)) {
            return 2;  //两对
        }
        $back = 0;
        if (self::isDaYuQDui($type)) {
            $back = 1; //对K或者大于
        }
        if (self::isShunZi($arr_card)) {
            $back = 4; //是否为顺子
        }
        if (self::isTongHua($arr_card)) {
            $back = 5; //是否为同花
        }
        if (self::isTongHuaShun()) {
            $back = 8; //是否为同花顺
        }
        if (self::isBigTongHuaShunByLaizi()) {
            $back = 10; //带赖子皇家同花顺
        }
        if (self::isBigTongHuaShun()) {
            $back = 11; //皇家同花顺
        }
        return $back;
    }

    /**
     * 找出牌型里那些牌需要高亮显示.
     * @param mixed $arr
     * @param mixed $type
     */
    public static function highLight($arr, $type)
    {
        $card_key = [];
        $card = self::getCard($arr);
        $val = array_count_values($card);
        if ($type > 3) {
            $card_key = $arr;
        } elseif ($type == 3) {
            //三条
            arsort($val);
            $kw = key($val);
            $card_key = [];
            foreach ($card as $k => $v) {
                if ($v == $kw || $k == self::$laizi_value) {
                    $card_key[] = $k;
                }
            }
        } elseif ($type == 2) {
            //两对
            $kw = $card_key = [];
            foreach ($val as $k => $v) {
                if ($v == 2) {
                    $kw[] = $k;
                }
            }
            foreach ($card as $k => $v) {
                if (in_array($v, $kw)) {
                    $card_key[] = $k;
                }
            }
        } elseif ($type == 1) {
            //对A后者对K
            foreach ($card as $k => $v) {
                if (in_array($v, ['A', 'K']) || $k == self::$laizi_value) {
                    $card_val[$k] = $v;
                }
            }
            $t_val = array_count_values($card_val);
            arsort($t_val);
            $kw = key($t_val);
            if (! self::$is_laizi) {
                if (count($t_val) > 1) {
                    foreach ($card_val as $k => $v) {
                        if ($kw != $v) {
                            unset($card_val[$k]);
                        }
                    }
                }
            } else {
                //去除k
                if (count($t_val) > 2) {
                    foreach ($card_val as $k => $v) {
                        if ($v == 'K') {
                            unset($card_val[$k]);
                        }
                    }
                }
            }
            $card_key = array_keys($card_val);
        }
        return $card_key;
    }

    /**
     * 是否翻倍， 玩家翻倍处理.
     * @param mixed $m_card
     * @param mixed $pos
     * @param mixed $arr
     */
    public static function getIsDoubleCard($m_card = 2, $pos = 2, $arr = [])
    {
        $list = self::$card_value_list;
        unset($list[self::$laizi_value]);  //去除赖子大王
        $card_list = array_rand($list, 4);
        //概率运算
        if (! empty($arr)) {
            $rate = self::_getRate($arr);
        } else {
            $rate = self::_getRate(self::$is_double_rate);
        }

        $min = $m_card % 16;
        //拿到最大牌A和最小牌2的概率需要特殊处理一下
        if ($min == 1 && $rate == 3) {
            //最大牌A出现, 对方肯定是平手或者输
            $rate = rand(1, 2);
        } elseif ($min == 2 && $rate == 1) {
            //最小牌2，出现对方肯定是平手或者赢 // $rate = rand(2,3);
            $rate = rand(2, 3);
        }
        //最小牌
        if ($rate == 2) {
            //不翻倍，奖励不扣除
            $key = $min;
        } elseif ($rate == 3) {
            //翻倍，奖励累加, 系统数， 发大牌
            if ($min == 13) {
                $key = 1;
            } else {
                $key = rand($min + 1, 13);
            }
        } else {
            //不翻倍，丢失全部奖励，系统赢发小牌
            if ($min == 1) {
                $key = rand(2, 13);
            } else {
                $key = rand(2, $min - 1);
            }
        }
        //根据key组牌
        $card_val = [$key, $key + 16, $key + 32, $key + 48];
        //去除相同的值
        $card_val = array_diff($card_val, $card_list);
        $card_key = array_rand($card_val, 1);
        $card_list[$pos] = $card_val[$card_key];
        return ['result' => $rate, 'msg' => self::$is_double_msg[$rate], 'm_card' => self::$card_value_list[$m_card], 'pos_card' => self::$card_value_list[$card_list[$pos]], 'pos' => $pos, 'card' => $card_list, 'show' => self::showCard($card_list)];
    }

    /**
     * 获取牌型结果.
     * @param mixed $arr
     */
    public static function getCardType($arr)
    {
        $type = self::checkCardType($arr);
        $highlight = self::highLight($arr, $type);
        return ['card' => $arr, 'type' => $type, 'typenote' => self::$card_type[$type], 'rate' => self::$card_rate[$type], 'highlight' => $highlight];
    }

    /**
     * 设置翻倍的概率.
     * @param mixed $rate
     */
    public static function setRate($rate = [])
    {
        if (empty($rate)) {
            self::$is_double_rate = $rate;
        }
    }

    /**
     * 去除赖子，并且排序.
     * @param mixed $arr
     */
    private static function exceptLaizi($arr)
    {
        $key = array_search(self::$laizi_value, $arr); //键值有可能0
        if ($key !== false) {
            unset($arr[$key]);
            self::$is_laizi = true;
        } else {
            self::$is_laizi = false;
        }
        sort($arr);
        return $arr;
    }

    /**
     * 获取牌内容，根据牌的key，获取牌的内容.
     * @param mixed $arr
     */
    private static function getCard($arr)
    {
        $card = [];
        foreach ($arr as $v) {
            $card[$v] = self::$card_value_list[$v];
        }
        return $card;
    }

    /**
     * 计算概率算法.
     * @param array $prizes 奖品概率数组
     *                      格式：array(奖品id => array( 'rate'=>概率),奖品id => array('rate'=>概率))
     * @param mixed $arr
     * @return int
     */
    private static function _getRate($arr = [])
    {
        $key = 0;
        //首先生成一个1W内的数
        $rid = rand(1, 10000);
        //概率值（按设置累加）
        $rate = 0;
        foreach ($arr as $k => $v) {
            //根据设置的概率向上累加
            $rate += $v;
            //如果生成的概率数小于或等于此数据，表示当前道具ID即是，退出查找
            if ($rid <= $rate) {
                $key = $k;
                break;
            }
        }
        return $key;
    }
}

/*

header("Content-type: text/html; charset=utf-8");

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
//类调用
$obj = new JokerPoker();

if($act == 'getcard') {
    //获取5张牌
    $key = $obj->getFiveCard();
    //$key = array(17,37,39,40,42);
    exit(json_encode($key));
} elseif($act == 'turncard') {
    //翻牌
    $tmp = isset($_REQUEST['card']) ? trim($_REQUEST['card']) : '';
    if(!empty($tmp)) {
        $key = explode('|',$tmp);
    } else {
        $key = array();
    }
    $key = array_map('intval', $key);
    $card = $obj->getFiveCard($key);
    $res = $obj->getCardType($card);
    exit(json_encode($res));
} elseif($act == 'isdouble') {
    //翻倍处理
    $card = isset($_REQUEST['card']) && !empty($_REQUEST['card']) ? intval($_REQUEST['card']) : 2;
    $pos = (isset($_REQUEST['pos']) && $_REQUEST['pos'] < 4) ? intval($_REQUEST['pos']) : 2;
    $res = $obj->getIsDoubleCard($card, $pos);
    exit(json_encode($res));
}

//测试牌型结果
$tmp = isset($_REQUEST['test']) ? trim($_REQUEST['test']) : '';
if(!empty($tmp)) {
    $key = explode('|',$tmp);
} else {
    $key = array();
}

//类调用
$obj = new JokerPoker();
$key = $obj->getFiveCard();
$key = array(13,18,24,27,43);
$card = $obj->showCard($key);

var_dump($key, $card, $obj->getCardType($key),$obj->getIsDoubleCard());

*/
