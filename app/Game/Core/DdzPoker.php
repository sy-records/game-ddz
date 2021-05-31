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
 * 斗地主 poker 算法逻辑.
 * @author：jiagnxinyu
 */
class DdzPoker
{
    //花色类型
    const COLOR_TYPE_HEITAO = 0; //黑桃

    const COLOR_TYPE_HONGTAO = 1; //红桃

    const COLOR_TYPE_MEIHUA = 2; //梅花

    const COLOR_TYPE_FANGKUAI = 3; //方块

    const COLOR_TYPE_XIAOWANG = 4; //小王

    const COLOR_TYPE_DAWANG = 5; //大王,  次类型暂时没用, 大小王花色类型都是4

    //牌显示出来的值
    const CARD_SAN = '3'; //牌值3

    const CARD_SI = '4'; //牌值4

    const CARD_WU = '5'; //牌值5

    const CARD_LIU = '6'; //牌值6

    const CARD_QI = '7'; //牌值7

    const CARD_BA = '8'; //牌值8

    const CARD_JIU = '9'; //牌值9

    const CARD_SHI = '10'; //牌值10

    const CARD_J = 'J'; //牌值J

    const CARD_Q = 'Q'; //牌值Q

    const CARD_K = 'K'; //牌值K

    const CARD_A = 'A'; //牌值A

    const CARD_ER = '2'; //牌值2

    const CARD_XIAOWANG = 'SJ'; //牌值小王

    const CARD_DAWANG = 'BJ'; //牌值大王

    //牌型
    const CARD_TYPE_DAN = 1; //单张

    const CARD_TYPE_DUI = 2; //对子

    const CARD_TYPE_SAN = 3; //三张

    const CARD_TYPE_SANDAIYI = 4; //三代一

    const CARD_TYPE_SANDAIER = 5; //三代二

    const CARD_TYPE_SHUNZI = 6; //顺子

    const CARD_TYPE_LIANDUI = 7; //连对

    const CARD_TYPE_FEIJIBUDAI = 8; //飞机不带

    const CARD_TYPE_FEIJIDAIDAN = 9; //飞机带单

    const CARD_TYPE_FEIJIDAISHUANG = 10; //飞机带双

    const CARD_TYPE_SIDAIYI = 11; //四带一, 指的是四带一对

    const CARD_TYPE_SIDAIER = 12; //四带二, 指的是四带两队

    const CARD_TYPE_ZHADAN = 13; //炸弹

    const CARD_TYPE_HUOJIAN = 14; //火箭

    /**
     * 构造花色值
     */
    public static $card_color = [
        self::COLOR_TYPE_HEITAO => '黑桃',
        self::COLOR_TYPE_HONGTAO => '红桃',
        self::COLOR_TYPE_MEIHUA => '梅花',
        self::COLOR_TYPE_FANGKUAI => '方块',
        self::COLOR_TYPE_XIAOWANG => '小王',
        self::COLOR_TYPE_DAWANG => '大王',
    ];

    /**
     * 构造扑克牌值列表(54张牌，采用16进制的模式， 每16位一种花色牌型，花色不一样, 大小王，固定值，这样设计，一个数字，既可以表示出牌值， 也能表示出花色）.
     * @var array
     */
    public static $card_value_list = [
        1 => self::CARD_SAN, 2 => self::CARD_SI, 3 => self::CARD_WU, 4 => self::CARD_LIU, 5 => self::CARD_QI, 6 => self::CARD_BA, 7 => self::CARD_JIU, 8 => self::CARD_SHI, 9 => self::CARD_J, 10 => self::CARD_Q, 11 => self::CARD_K, 12 => self::CARD_A, 13 => self::CARD_ER,
        17 => self::CARD_SAN, 18 => self::CARD_SI, 19 => self::CARD_WU, 20 => self::CARD_LIU, 21 => self::CARD_QI, 22 => self::CARD_BA, 23 => self::CARD_JIU, 24 => self::CARD_SHI, 25 => self::CARD_J, 26 => self::CARD_Q, 27 => self::CARD_K, 28 => self::CARD_A, 29 => self::CARD_ER,
        33 => self::CARD_SAN, 34 => self::CARD_SI, 35 => self::CARD_WU, 36 => self::CARD_LIU, 37 => self::CARD_QI, 38 => self::CARD_BA, 39 => self::CARD_JIU, 40 => self::CARD_SHI, 41 => self::CARD_J, 42 => self::CARD_Q, 43 => self::CARD_K, 44 => self::CARD_A, 45 => self::CARD_ER,
        49 => self::CARD_SAN, 50 => self::CARD_SI, 51 => self::CARD_WU, 52 => self::CARD_LIU, 53 => self::CARD_QI, 54 => self::CARD_BA, 55 => self::CARD_JIU, 56 => self::CARD_SHI, 57 => self::CARD_J, 58 => self::CARD_Q, 59 => self::CARD_K, 60 => self::CARD_A, 61 => self::CARD_ER,
        78 => self::CARD_XIAOWANG,
        79 => self::CARD_DAWANG,
    ];

    /**
     * 构造牌型值
     * @var array
     */
    public static $card_type = [
        self::CARD_TYPE_DAN => '单张',
        self::CARD_TYPE_DUI => '对子',
        self::CARD_TYPE_SAN => '三张',
        self::CARD_TYPE_SANDAIYI => '三带一',
        self::CARD_TYPE_SANDAIER => '三带二',
        self::CARD_TYPE_SHUNZI => '顺子',
        self::CARD_TYPE_LIANDUI => '连对',
        self::CARD_TYPE_FEIJIBUDAI => '飞机不带',
        self::CARD_TYPE_FEIJIDAIDAN => '飞机带单',
        self::CARD_TYPE_FEIJIDAISHUANG => '飞机带双',
        self::CARD_TYPE_SIDAIYI => '四带一对',
        self::CARD_TYPE_SIDAIER => '四带二对',
        self::CARD_TYPE_ZHADAN => '炸弹',
        self::CARD_TYPE_HUOJIAN => '火箭',
    ];

    /*
     * 发牌
     */
    public function dealCards($users = [])
    {
        $cards = array_keys(self::$card_value_list);
        //洗牌
        $user_card1 = $user_card2 = $user_card3 = $hand = [];
        shuffle($cards);
        //每人发17张牌
        $chuank = array_chunk($cards, 51);
        $hand = $chuank[1];
        $cards = $chuank[0];
        $cnt = count($cards);
        for ($i = 0; $i < $cnt; $i += 3) {
            $user_card1[] = $cards[$i];
            $user_card2[] = $cards[$i + 1];
            $user_card3[] = $cards[$i + 2];
        }
        $user_card1 = $this->_sortCardByGrade($user_card1);
        $user_card2 = $this->_sortCardByGrade($user_card2);
        $user_card3 = $this->_sortCardByGrade($user_card3);
        if (! empty($users)) {
            $card['hand'] = $hand;
            foreach ($users as $k => $v) {
                $str = 'user_card' . ($k + 1);
                $tmp = ${$str};
                $card[$v] = $tmp;
                $show_card[$v] = $this->crateCard($tmp);
            }
        } else {
            $card = ['user1' => $user_card1, 'user2' => $user_card2, 'user3' => $user_card3, 'hand' => $hand];
            $show_card = ['user1' => $this->crateCard($user_card1), 'user2' => $this->crateCard($user_card2), 'user3' => $this->crateCard($user_card3), 'hand' => $this->crateCard($hand)];
        }
        return ['card' => $card, 'show_card' => $show_card];
    }

    /**
     * 根据牌值构建牌.
     * @param $card
     * @return array
     */
    public function crateCard($card)
    {
        $data = [];
        foreach ($card as $v) {
            if ($v == 78) {
                $color_type = self::COLOR_TYPE_XIAOWANG;
            } elseif ($v == 79) {
                $color_type = self::COLOR_TYPE_DAWANG;
            } else {
                $color_type = intval($v / 16);
            }
            $color = self::$card_color[$color_type];
            $data[$v] = $color . '_' . self::$card_value_list[$v];
        }
        return $data;
    }

    /**
     * 判断是否为单张牌.
     * @param $arr_card
     * @return bool
     */
    public function isDan($arr_card)
    {
        if (count($arr_card) == 1) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否为对子.
     * @param $arr_card
     * @return bool
     */
    public function isDui($arr_card)
    {
        if (count($arr_card) == 2 && ($this->_getModVal($arr_card[0]) == $this->_getModVal($arr_card[1]))) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否为三张.
     * @param $arr_card
     * @return bool
     */
    public function isSan($arr_card)
    {
        $value = $this->_getModVal($arr_card[0]);
        if (count($arr_card) == 3 && ($this->_getModVal($arr_card[1]) == $value && $this->_getModVal($arr_card[2]) == $value)) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否为三带一
     * @param $arr_card
     * @return bool
     */
    public function isSanDaiYi($arr_card)
    {
        $back = false;
        if (count($arr_card) == 4) {
            //排序
            $arr_card = $this->_sortCardByGrade($arr_card);
            if ($this->_getModVal($arr_card[0]) == $this->_getModVal($arr_card[1]) && $this->_getModVal($arr_card[1]) == $this->_getModVal($arr_card[2]) && $this->_getModVal($arr_card[2]) != $this->_getModVal($arr_card[3])) {
                //带单在后面
                $back = true;
            } elseif ($this->_getModVal($arr_card[0]) != $this->_getModVal($arr_card[1]) && $this->_getModVal($arr_card[1]) == $this->_getModVal($arr_card[2]) && $this->_getModVal($arr_card[2]) == $this->_getModVal($arr_card[3])) {
                //带单在前面
                $back = true;
            }
        }
        return $back;
    }

    /**
     * 判断是否为三带二.
     * @param $arr_card
     * @return bool
     */
    public function isSanDaiEr($arr_card)
    {
        $back = false;
        if (count($arr_card) == 5) {
            //排序
            $arr_card = $this->_sortCardByGrade($arr_card);
            if ($this->_getModVal($arr_card[0]) == $this->_getModVal($arr_card[1]) && $this->_getModVal($arr_card[1]) == $this->_getModVal($arr_card[2]) && $this->_getModVal($arr_card[2]) != $this->_getModVal($arr_card[3]) && $this->_getModVal($arr_card[3]) == $this->_getModVal($arr_card[4])) {
                //带单在后面
                $back = true;
            } elseif ($this->_getModVal($arr_card[0]) == $this->_getModVal($arr_card[1]) && $this->_getModVal($arr_card[1]) != $this->_getModVal($arr_card[2]) && $this->_getModVal($arr_card[2]) == $this->_getModVal($arr_card[3]) && $this->_getModVal($arr_card[3]) == $this->_getModVal($arr_card[4])) {
                //带单在前面
                $back = true;
            }
        }
        return $back;
    }

    /**
     * 判断是否为顺子.
     * @param $arr_card
     * @return bool
     */
    public function isShunZi($arr_card)
    {
        $cnt = count($arr_card);
        if ($cnt < 5 || $cnt > 12) {
            return false;
        }
        //排序
        $arr_card = $this->_sortCardByGrade($arr_card);
        for ($i = 0; $i < $cnt - 1; ++$i) {
            //过滤掉2，小王,大王
            if (in_array($this->_getModVal($arr_card[$i]), [13, 14, 15])) {
                return false;
            }
            if ($this->_getModVal($arr_card[$i + 1]) - $this->_getModVal($arr_card[$i]) != 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断是否为连对.
     * @param $arr_card
     * @return bool
     */
    public function isLianDui($arr_card)
    {
        $cnt = count($arr_card);
        if ($cnt < 6 || $cnt % 2 != 0) {
            return false;
        }
        //排序
        $arr_card = $this->_sortCardByGrade($arr_card);
        for ($i = 0; $i < $cnt - 1; $i = $i + 2) {
            //过滤掉2，小王,大王
            if (in_array($this->_getModVal($arr_card[$i]), [13, 14, 15])) {
                return false;
            }
            if ($this->_getModVal($arr_card[$i]) != $this->_getModVal($arr_card[$i + 1])) {
                return false;
            }
            if ($i < $cnt - 2) {
                if ($this->_getModVal($arr_card[$i]) - $this->_getModVal($arr_card[$i + 2]) != -1) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 判断是否为飞机不带.
     * @param $arr_card
     * @return bool
     */
    public function isFeiJiBuDai($arr_card)
    {
        $cnt = count($arr_card);
        if ($cnt < 6 || $cnt % 3 != 0) {
            return false;
        }
        //排序
        $arr_card = $this->_sortCardByGrade($arr_card);
        $index = [];
        for ($i = 0; $i < $cnt - 2; $i = $i + 3) {
            //过滤掉2，小王,大王
            if (in_array($this->_getModVal($arr_card[$i]), [13, 14, 15])) {
                return false;
            }
            if ($i != $cnt) {
                if (! $this->isSan([$arr_card[$i], $arr_card[$i + 1], $arr_card[$i + 2]])) {
                    return false;
                }
                $index[] = $arr_card[$i];
            }
        }
        //排序
        $index = $this->_sortCardByGrade($index);
        $index_cnt = count($index);
        for ($i = 0; $i < $index_cnt - 1; ++$i) {
            if ($this->_getModVal($index[$i]) - $this->_getModVal($index[$i + 1]) != -1) {
                return false;
            }
        }
        return true;
    }

    /**
     * 是非为飞机带单.
     * @param $arr_card
     * @param int $flag 默认为1时， 飞机带单， 等于2是，判断飞机带双
     * @return bool
     */
    public function isFeiJiDaiDan($arr_card, $flag = 1)
    {
        $cnt = count($arr_card);
        if ($cnt < 8) {
            return false;
        }
        //对牌值进行取模运算
        $card_value = [];
        array_walk(
            $arr_card,
            function ($item, $key) use (&$card_value) {
                $card_value[$key] = $this->_getModVal($item);
            }
        );
        $arrs = array_count_values($card_value);
        $list = [];
        foreach ($arrs as $k => $v) {
            if ($v == 3) {
                $list[] = $k;
            }
        }
        //排序
        $list = $this->_sortCardByGrade($list);
        for ($i = 0; $i < count($list) - 1; ++$i) {
            if ($list[$i] - $list[$i + 1] != -1) {
                return false;
            }
        }
        $list_cnt = count($list);
        $pokers = $list_cnt * 3 + $list_cnt * $flag;
        if ($pokers != count($arr_card)) {
            return false;
        }
        return true;
    }

    /**
     * 判断是否为飞机带双.
     * @param $arr_card
     * @return bool
     */
    public function isFeiJiDaiShuang($arr_card)
    {
        return $this->isFeiJiDaiDan($arr_card, $flag = 2);
    }

    /**
     * 判断是否为四带一, 表示四带一对.
     * @param $arr_card
     * @return bool
     */
    public function isSiDaiYi($arr_card)
    {
        //排序
        $arr_card = $this->_sortCardByGrade($arr_card);
        $cnt = count($arr_card);
        $back = false;
        if ($cnt == 6) {
            $grade0 = $this->_getModVal($arr_card[0]);
            $grade1 = $this->_getModVal($arr_card[1]);
            $grade2 = $this->_getModVal($arr_card[2]);
            $grade3 = $this->_getModVal($arr_card[3]);
            $grade4 = $this->_getModVal($arr_card[4]);
            $grade5 = $this->_getModVal($arr_card[5]);
            if ($grade0 == $grade1 && $grade1 == $grade2 && $grade2 == $grade3 && $grade3 != $grade4 && $grade4 == $grade5) {
                $back = true;
            } elseif ($grade0 == $grade1 && $grade1 != $grade2 && $grade2 == $grade3 && $grade3 == $grade4 && $grade4 == $grade5) {
                $back = true;
            }
        }
        return $back;
    }

    /**
     * 判断是否为四带二, 表示4带2对.
     * @param $arr_card
     * @return bool
     */
    public function isSiDaiEr($arr_card)
    {
        //排序
        $arr_card = $this->_sortCardByGrade($arr_card);
        $cnt = count($arr_card);
        $back = false;
        if ($cnt == 8) {
            $grade0 = $this->_getModVal($arr_card[0]);
            $grade1 = $this->_getModVal($arr_card[1]);
            $grade2 = $this->_getModVal($arr_card[2]);
            $grade3 = $this->_getModVal($arr_card[3]);
            $grade4 = $this->_getModVal($arr_card[4]);
            $grade5 = $this->_getModVal($arr_card[5]);
            $grade6 = $this->_getModVal($arr_card[6]);
            $grade7 = $this->_getModVal($arr_card[7]);
            if ($grade0 == $grade1 && $grade1 == $grade2 && $grade2 == $grade3 && $grade3 != $grade4 && $grade4 == $grade5 && $grade6 == $grade7) {
                $back = true;
            } elseif ($grade0 == $grade1 && $grade1 != $grade2 && $grade2 == $grade3 && $grade4 == $grade5 && $grade5 == $grade6 && $grade6 == $grade7) {
                $back = true;
            } elseif ($grade0 == $grade1 && $grade1 != $grade2 && $grade2 == $grade3 && $grade3 == $grade4 && $grade4 == $grade5 && $grade5 != $grade6 && $grade6 == $grade7) {
                $back = true;
            }
        }
        return $back;
    }

    /**
     * 是否为炸弹.
     * @param $arr_card
     * @return bool
     */
    public function isZha($arr_card)
    {
        if (count($arr_card) == 4 && $this->_getModVal($arr_card[0]) == $this->_getModVal($arr_card[1]) && $this->_getModVal($arr_card[1]) == $this->_getModVal($arr_card[2]) && $this->_getModVal($arr_card[2]) == $this->_getModVal($arr_card[3])) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否为火箭.
     * @param $arr_card
     * @return bool
     */
    public function isHuojian($arr_card)
    {
        if (count($arr_card) == 2 && (($arr_card[0] == 78 && $arr_card[1] == 79) || ($arr_card[1] == 79 && $arr_card[2] == 80))) {
            return true;
        }
        return false;
    }

    /**
     * 检测牌型.
     * @param $arr_card
     * @return array
     */
    public function checkCardType($arr_card)
    {
        $type = 0;
        if ($this->isDan($arr_card)) {
            $type = self::CARD_TYPE_DAN;
        } elseif ($this->isDui($arr_card)) {
            $type = self::CARD_TYPE_DUI;
        } elseif ($this->isHuojian($arr_card)) {
            $type = self::CARD_TYPE_HUOJIAN;
        } elseif ($this->isSan($arr_card)) {
            $type = self::CARD_TYPE_SAN;
        } elseif ($this->isZha($arr_card)) {
            $type = self::CARD_TYPE_ZHADAN;
        } elseif ($this->isSandaiyi($arr_card)) {
            $type = self::CARD_TYPE_SANDAIYI;
        } elseif ($this->isShunzi($arr_card)) {
            $type = self::CARD_TYPE_SHUNZI;
        } elseif ($this->isLiandui($arr_card)) {
            $type = self::CARD_TYPE_LIANDUI;
        } elseif ($this->isFeijiBuDai($arr_card)) {
            $type = self::CARD_TYPE_FEIJIBUDAI;
        } elseif ($this->isSiDaiYi($arr_card)) {
            $type = self::CARD_TYPE_SIDAIYI;
        } elseif ($this->isSiDaiEr($arr_card)) {
            $type = self::CARD_TYPE_SIDAIER;
        } elseif ($this->isSandaiEr($arr_card)) {
            $type = self::CARD_TYPE_SANDAIER;
        } elseif ($this->isFeijiDaiDan($arr_card)) {
            $type = self::CARD_TYPE_FEIJIDAIDAN;
        } elseif ($this->isFeijiDaiShuang($arr_card)) {
            $type = self::CARD_TYPE_FEIJIDAISHUANG;
        }
        if (array_key_exists($type, self::$card_type)) {
            $back = ['type' => $type, 'type_msg' => self::$card_type[$type]];
        } else {
            $back = ['type' => $type, 'type_msg' => 'unknow']; //未知牌型
        }
        return $back;
    }

    /**
     * 检测牌的大小
     * 比较2家的牌，主要有2种情况:
     * 1.我出和上家一种类型的牌，即对子管对子；
     * 2.我出炸弹，此时，和上家的牌的类型可能不同
     * 王炸的情况先排除.
     * @param array $my 我的出牌， 格式：array(）
     * @param array $prev 上手出牌， 格式：array(）
     * @return bool，true是可以出牌， false不能出牌
     */
    public function checkCardSize($my = [], $prev = [])
    {
        //判断我的牌数据是否非法
        if (! $my) {
            return false;
        }
        //计算出我的牌型
        $my_arr = $this->checkCardType($my);
        $my_type = isset($my_arr['type']) ? $my_arr['type'] : 0;
        //我先出牌上家没牌， 我大
        if (! $prev) {
            return true;
        }
        $prev_arr = $this->checkCardType($prev);
        $prev_type = isset($prev_arr['type']) ? $prev_arr['type'] : 0;
        //集中判断是否为火箭，免得多次判断火箭
        if ($my_type == self::CARD_TYPE_HUOJIAN) {
            return true;
        }
        if ($prev_type == self::CARD_TYPE_HUOJIAN) {
            return false;
        }
        //集中判断上家不是炸弹，我出炸弹的情况
        if ($my_type == self::CARD_TYPE_ZHADAN && $prev_type != self::CARD_TYPE_ZHADAN) {
            return true;
        }
        $my_card = $this->_sortCardByGrade($my);
        $prev_card = $this->_sortCardByGrade($prev);
        $my_cnt = count($my_card);
        $prev_cnt = count($prev_card);

        if ($my_type == self::CARD_TYPE_DAN && $prev_type == self::CARD_TYPE_DAN && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //单张
            return true;
        }
        if ($my_type == self::CARD_TYPE_DUI && $prev_type == self::CARD_TYPE_DUI && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //对子
            return true;
        }
        if ($my_type == self::CARD_TYPE_SAN && $prev_type == self::CARD_TYPE_SAN && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //三张
            return true;
        }
        if ($my_type == self::CARD_TYPE_ZHADAN && $prev_type == self::CARD_TYPE_ZHADAN && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //炸弹
            return true;
        }
        if ($my_type == self::CARD_TYPE_SANDAIYI && $prev_type == self::CARD_TYPE_SANDAIYI && $this->_getModVal($my_card[1]) > $this->_getModVal($prev_card[1])) {
            //三带一，只比较第二张牌的大小
            return true;
        }
        if ($my_type == self::CARD_TYPE_SANDAIER && $prev_type == self::CARD_TYPE_SANDAIER && $this->_getModVal($my_card[2]) > $this->_getModVal($prev_card[2])) {
            //三带二，只比较第三张牌的大小
            return true;
        }
        if ($my_type == self::CARD_TYPE_SIDAIYI && $prev_type == self::CARD_TYPE_SIDAIYI && $this->_getModVal($my_card[2]) > $this->_getModVal($prev_card[2])) {
            //四带一对，只比较第二张牌的大小
            return true;
        }
        if ($my_type == self::CARD_TYPE_SIDAIER && $prev_type == self::CARD_TYPE_SIDAIER) {
            //对牌值进行取模运算
            $my_card_grade = $prev_card_grade = [];
            array_walk(
                $my_card,
                function ($item, $key) use (&$my_card_grade) {
                    $my_card_grade[$key] = $this->_getModVal($item);
                }
            );
            array_walk(
                $prev_card,
                function ($item, $key) use (&$prev_card_grade) {
                    $prev_card_grade[$key] = $this->_getModVal($item);
                }
            );
            $m = array_flip(array_count_values($my_card_grade));
            $p = array_flip(array_count_values($prev_card_grade));
            if (isset($m[4], $p[4]) && $m[4] > $p[4]) {
                return true;
            }
        } elseif ($my_type == self::CARD_TYPE_SHUNZI && $prev_type == self::CARD_TYPE_SHUNZI && $my_cnt == $prev_cnt && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //顺子
            return true;
        } elseif ($my_type == self::CARD_TYPE_LIANDUI && $prev_type == self::CARD_TYPE_LIANDUI && $my_cnt == $prev_cnt && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //连对
            return true;
        } elseif ($my_type == self::CARD_TYPE_FEIJIBUDAI && $prev_type == self::CARD_TYPE_FEIJIBUDAI && $my_cnt == $prev_cnt && $this->_getModVal($my_card[0]) > $this->_getModVal($prev_card[0])) {
            //飞机不带
            return true;
        } elseif ($my_type == self::CARD_TYPE_FEIJIDAIDAN && $prev_type == self::CARD_TYPE_FEIJIDAIDAN && $my_cnt == $prev_cnt && $this->_getModVal($my_card[1]) > $this->_getModVal($prev_card[1])) {
            //飞机带单
            return true;
        } elseif ($my_type == self::CARD_TYPE_FEIJIDAISHUANG && $prev_type == self::CARD_TYPE_FEIJIDAISHUANG && $my_cnt == $prev_cnt && $this->_getModVal($my_card[2]) > $this->_getModVal($prev_card[2])) {
            //飞机带双
            return true;
        }
        return false;
    }

    /**
     * 是否可以出牌.
     * @param $my_card
     * @param $prev_card
     * @return bool
     */
    public function isPlayCard($my_card, $prev_card)
    {
        //我的手牌和上家手牌不能为空
        if (empty($my_card) || empty($prev_card)) {
            return false;
        }
        $arr = $this->checkCardType($prev_card);
        $prev_type = isset($arr['type']) ? $arr['type'] : 0;
        //手牌类型
        if (! array_key_exists($prev_type, self::$card_type)) {
            return false;
        }
        $my_card = $this->_sortCardByGrade($my_card);
        $prev_card = $this->_sortCardByGrade($prev_card);
        $my_cnt = count($my_card);
        $prev_cnt = count($prev_card);
        //我先出牌， 上家没有牌
        if ($prev_cnt == 0 && $my_cnt > 0) {
            return true;
        }
        //集中判断是否为火箭， 免得后面多次判断
        if ($prev_type == self::CARD_TYPE_HUOJIAN) {
            return false;
        }
        //判断我的牌里是否有飞机, 如果有飞机, 肯定可以出牌
        if ($my_cnt >= 2 && $this->isHuojian([$my_card[$my_cnt - 1]], $my_card[$my_cnt - 2])) {
            return true;
        }
        //集中判断对方不是炸弹，我出炸弹的情况
        if ($prev_type != self::CARD_TYPE_ZHADAN) {
            if ($my_cnt < 4) {
                return false;
            }
            //循环判断， 我方是否有炸弹
            for ($i = 0; $i < $my_cnt - 3; ++$i) {
                $grade0 = $this->_getModVal($my_card[$i]);
                $grade1 = $this->_getModVal($my_card[$i + 1]);
                $grade2 = $this->_getModVal($my_card[$i + 2]);
                $grade3 = $this->_getModVal($my_card[$i + 3]);
                if ($grade0 == $grade1 && $grade2 == $grade0 && $grade3 == $grade0) {
                    return true;
                }
            }
        }
        //上家出单张
        if ($prev_type == self::CARD_TYPE_DAN) {
            //最要判断最后一张牌是否能大过上家的牌就行
            if ($this->_getModVal($my_card[$my_cnt - 1]) > $this->_getModVal($prev_card[0])) {
                return true;
            }
        } //上家出对子
        elseif ($prev_type == self::CARD_TYPE_DUI) {
            // 2张牌可以大过上家的牌
            for ($i = $my_cnt - 1; $i >= 1; --$i) {
                $grade0 = $this->_getModVal($my_card[$i]);
                $grade1 = $this->_getModVal($my_card[$i - 1]);
                if ($grade0 == $grade1 && $grade0 > $this->_getModVal($prev_card[0])) {
                    return true;
                }
            }
        } //上家出三不带, 三带一， 三带二
        elseif (in_array($prev_type, [self::CARD_TYPE_SAN, self::CARD_TYPE_SANDAIYI, self::CARD_TYPE_SANDAIER])) {
            //区别在于三带一喝三带二要判断牌数
            if ($prev_type == self::CARD_TYPE_SANDAIYI && $my_cnt < 4) {
                return false;
            }
            if ($prev_type == self::CARD_TYPE_SANDAIER && $my_cnt < 5) {
                return false;
            }
            // 3张牌可以大过上家的牌
            for ($i = $my_cnt - 1; $i >= 2; --$i) {
                $grade0 = $this->_getModVal($my_card[$i]);
                $grade1 = $this->_getModVal($my_card[$i - 1]);
                $grade2 = $this->_getModVal($my_card[$i - 2]);
                if ($grade0 == $grade1 && $grade0 == $grade2 && $grade0 > $this->_getModVal($prev_card[2])) {
                    return true;
                }
            }
        } //上家出炸弹，四带一对，四带二对， 只要手牌有炸弹，或有对，就可以出牌就可以出牌
        elseif ($prev_type == self::CARD_TYPE_ZHADAN || $prev_type == self::CARD_TYPE_SIDAIYI || $prev_type == self::CARD_TYPE_SIDAIER) {
            // 4张牌可以大过上家的牌
            $dui_cnt = 0; //手牌对子计数
            $si_arr = []; //四张牌值grade
            for ($i = $my_cnt - 1; $i >= 3; --$i) {
                $grade0 = $this->_getModVal($my_card[$i]);
                $grade1 = $this->_getModVal($my_card[$i - 1]);
                $grade2 = $this->_getModVal($my_card[$i - 2]);
                $grade3 = $this->_getModVal($my_card[$i - 3]);
                //记录四张相同的牌值
                if ($grade0 == $grade1 && $grade0 == $grade2 && $grade0 == $grade3) {
                    $si_arr[] = $grade0;
                } else {
                    //统计对子数量
                    if ($grade0 == $grade1 || $grade1 == $grade2 || $grade2 == $grade3) {
                        ++$dui_cnt;
                    }
                }
            }
            //和上级比较四张的牌值
            foreach ($si_arr as $v) {
                if ($prev_type == self::CARD_TYPE_ZHADAN && $v > $this->_getModVal($prev_card[0])) {
                    return true;
                }
                if ($prev_type == self::CARD_TYPE_SIDAIYI && $v > $this->_getModVal($prev_card[2]) && $dui_cnt > 0) {
                    return true;
                }
                if ($prev_type == self::CARD_TYPE_SIDAIER && $dui_cnt > 1) {
                    //四带两对的三种情况
                    $prev_grade1 = $this->_getModVal($prev_card[1]);
                    $prev_grade2 = $this->_getModVal($prev_card[2]);
                    $prev_grade3 = $this->_getModVal($prev_card[3]);
                    $prev_grade5 = $this->_getModVal($prev_card[5]);
                    $prev_grade6 = $this->_getModVal($prev_card[6]);
                    if ($prev_grade1 == $prev_grade2 && $v > $prev_grade1) {
                        return true;
                    }
                    if ($prev_grade5 == $prev_grade6 && $v > $prev_grade5) {
                        return true;
                    }
                    if ($prev_grade1 != $prev_grade2 && $prev_grade5 != $prev_grade6 && $v > $prev_grade3) {
                        return true;
                    }
                }
            }
        } //上家出顺子, 出连对, 飞机
        elseif ($prev_type == self::CARD_TYPE_SHUNZI || $prev_type == self::CARD_TYPE_LIANDUI || $prev_type == self::CARD_TYPE_FEIJIBUDAI || $prev_type == self::CARD_TYPE_FEIJIDAIDAN || $prev_type == self::CARD_TYPE_FEIJIDAISHUANG) {
            if ($my_cnt < $prev_cnt) {
                return false;
            }
            $my_card_grade = $this->_getCardGrade($my_card);
            $prev_card_grade = $this->_getCardGrade($prev_card);
            $tmp_my_cnt = array_count_values($my_card_grade); //统计出牌的grade值相同张数
                $my_card_grade = array_keys(array_flip($my_card_grade)); //去重
                //飞机带单和飞机带双要特殊处理一下
                if ($prev_type == self::CARD_TYPE_FEIJIDAIDAN || $prev_type == self::CARD_TYPE_FEIJIDAISHUANG) {
                    $tmp_prev_cnt = array_count_values($prev_card_grade);
                    $prev_card_grade = [];
                    foreach ($tmp_prev_cnt as $k => $v) {
                        if ($v == 3) {
                            $prev_card_grade[] = $k;
                        }
                    }
                } else {
                    $prev_card_grade = array_keys(array_flip($prev_card_grade)); //去重
                }
            $my_cnt = count($my_card_grade);
            $prev_cnt = count($prev_card_grade);
            for ($i = $my_cnt - 1; $i >= $prev_cnt - 1; --$i) {
                $my_tmp_cards = [];
                for ($j = 0; $j < $prev_cnt; ++$j) {
                    $my_tmp_cards[] = $my_card_grade[$i - $j];
                }
                $my_tmp_cards = $this->_sortCardByGrade($my_tmp_cards);
                if ($prev_type == self::CARD_TYPE_SHUNZI) {
                    //检查牌的类型
                    $tmp_type = $this->checkCardType($my_tmp_cards);
                    $my_type = isset($tmp_type['type']) ? $tmp_type['type'] : 0;
                    $grade = $my_tmp_cards[count($my_tmp_cards) - 1]; // 最大的牌在最后
                        $prev_grade = $prev_card_grade[$prev_cnt - 1]; // 最大的牌在最后
                        if ($my_type == $prev_type && $grade > $prev_grade) {
                            return true;
                        }
                } elseif ($prev_type == self::CARD_TYPE_LIANDUI) {
                    if ($this->_isContinuous($my_tmp_cards) && $this->_isNumOk($tmp_my_cnt, $my_tmp_cards, 2)) {
                        return true;
                    }
                } elseif ($prev_type == self::CARD_TYPE_FEIJIBUDAI) {
                    //判断连续性
                    if ($this->_isContinuous($my_tmp_cards) && $this->_isNumOk($tmp_my_cnt, $my_tmp_cards, 3)) {
                        return true;
                    }
                } elseif ($prev_type == self::CARD_TYPE_FEIJIDAIDAN) {
                    //判断连续性
                    if ($this->_isContinuous($my_tmp_cards) && $this->_isNumOk($tmp_my_cnt, $my_tmp_cards, 3) && $this->_isDaiNumOk($tmp_my_cnt, $my_tmp_cards)) {
                        return true;
                    }
                } elseif ($prev_type == self::CARD_TYPE_FEIJIDAISHUANG) {
                    //判断连续性
                    if ($this->_isContinuous($my_tmp_cards) && $this->_isNumOk($tmp_my_cnt, $my_tmp_cards, 3) && $this->_isDaiNumOk($tmp_my_cnt, $my_tmp_cards, 2)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 是否好牌.
     * @param array $cards
     * @return bool
     */
    public function isGoodCard($cards = [])
    {
        //判断牌里是否有大王和小王， 还有两张2的也算好牌
        $er = 0;
        foreach ($cards as $v) {
            if ($v == 79) {
                return true;
            }
            if ($this->_getModVal($v) == 13) {
                ++$er;
                if ($er >= 2) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 对手牌进行排序处理.
     * @param array $card
     * @return array
     */
    public function _sortCardByGrade($card = [])
    {
        //牌进行排序
        $new_card = [];
        foreach ($card as $v) {
            $new_card[$v] = $this->_getModVal($v);
        }
        //对数组按值排序, 并保留键值
        asort($new_card);
        $card = [];
        foreach ($new_card as $k => $v) {
            $card[] = $k;
        }
        return $card;
    }

    /**
     * 最值进行取模运算， 获取到牌的值
     * @param $val
     * @return int
     */
    private function _getModVal($val)
    {
        return $val % 16;
    }

    /**
     * 获取牌的grade值
     * @param $cards
     * @return array
     */
    private function _getCardGrade($cards)
    {
        $new_card = [];
        foreach ($cards as $v) {
            $new_card[] = $this->_getModVal($v);
        }
        return $new_card;
    }

    /**
     * 判断牌值是否连续.
     * @param $card
     * @return bool
     */
    private function _isContinuous($card)
    {
        $card = $this->_sortCardByGrade($card);
        $cnt = count($card);
        for ($i = 0; $i < $cnt - 1; ++$i) {
            if ($card[$i] - $card[$i + 1] != -1) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断数量是否正确, 顺子,连对,飞机使用判断.
     * @param $cnt_card
     * @param $card
     * @param int $num
     * @return bool
     */
    private function _isNumOk($cnt_card, $card, $num = 2)
    {
        //判断数量
        foreach ($card as $v) {
            if ($cnt_card[$v] < $num) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断戴牌的数量是否ok, 主要用户飞机带单和飞机带双使用.
     * @param $cnt_card
     * @param $card
     * @param int $num 1表示带单， 2表示带双
     * @return bool
     */
    private function _isDaiNumOk($cnt_card, $card, $num = 1)
    {
        //判断数量
        $count = 0;
        foreach ($cnt_card as $k => $v) {
            if (! in_array($k, $card) && $v >= $num) {
                ++$count;
            }
        }
        if ($count >= count($card)) {
            return true;
        }
        return false;
    }
}

/*
//测试用例
$obj = new DdzPoker();
echo '<pre>';
var_dump('测试发牌:----------------------------',$obj->dealCards());
var_dump('测试检查牌型:单张----------------------------',$obj->checkCardType(array(5)));
var_dump('测试检查牌型:对子----------------------------',$obj->checkCardType(array(6, 22)));
var_dump('测试检查牌型:火箭----------------------------',$obj->checkCardType(array(78, 79)));
var_dump('测试检查牌型:三张----------------------------',$obj->checkCardType(array(8, 24, 40)));
var_dump('测试检查牌型:炸弹----------------------------',$obj->checkCardType(array(6, 22, 38, 54)));
var_dump('测试检查牌型:三带一----------------------------',$obj->checkCardType(array(6, 22, 38, 5)));
var_dump('测试检查牌型:顺子----------------------------',$obj->checkCardType(array(8, 9, 10, 11, 12)));
var_dump('测试检查牌型:连对----------------------------',$obj->checkCardType(array(8, 24, 9, 25, 10, 26, 11, 27, 12, 28)));
var_dump('测试检查牌型:飞机不带----------------------------',$obj->checkCardType(array(8, 24, 40, 9, 25, 41)));
var_dump('测试检查牌型:四带一----------------------------',$obj->checkCardType(array(5, 21, 37, 53, 6, 22)));
var_dump('测试检查牌型:四带二----------------------------',$obj->checkCardType(array(5, 21, 37, 53, 6, 22, 7, 23)));
var_dump('测试检查牌型:三带二----------------------------',$obj->checkCardType(array(6, 22, 38, 5, 53)));
var_dump('测试检查牌型:飞机带单----------------------------',$obj->checkCardType(array(5, 21, 37, 6, 22, 38, 7, 23, 39, 2, 3, 4)));
var_dump('测试检查牌型:飞机带双----------------------------',$obj->checkCardType(array(5, 21, 37, 6, 22, 38, 7, 23, 39, 2, 18, 3, 19, 4, 20)));

var_dump('测试比牌大小:单张----------------------------',$obj->checkCardSize(array(13), array(11)));
var_dump('测试比牌大小:对子----------------------------',$obj->checkCardSize(array(8,24), array(7,23)));
var_dump('测试比牌大小:三张----------------------------',$obj->checkCardSize(array(8,24,40), array(7,23,39)));
var_dump('测试比牌大小:三带一----------------------------',$obj->checkCardSize(array(8,24,40,4), array(7,23,39,5)));
var_dump('测试比牌大小:三带二----------------------------',$obj->checkCardSize(array(8,24,40,4,20), array(7,23,39,5,21)));
var_dump('测试比牌大小:顺子----------------------------',$obj->checkCardSize(array(8,9,10,11,12), array(7,24,25,42,27)));
var_dump('测试比牌大小:连对----------------------------',$obj->checkCardSize(array(8,9,10,11,12), array(7,24,25,42,27)));
var_dump('测试比牌大小:飞机不带----------------------------',$obj->checkCardSize(array(8,24,40), array(7,23,39)));
var_dump('测试比牌大小:飞机带单----------------------------',$obj->checkCardSize(array(8,24,40,4), array(7,23,39,3)));
var_dump('测试比牌大小:飞机带双----------------------------',$obj->checkCardSize(array(8,24,40,4,20), array(7,23,39,3,19)));
var_dump('测试比牌大小:四带一----------------------------',$obj->checkCardSize(array(8,24,40,56,4,20), array(7,23,39,55,3,19)));
var_dump('测试比牌大小:四带二----------------------------',$obj->checkCardSize(array(8,24,40,56,4,20,6,22), array(7,23,39,55,3,19,10,26)));
var_dump('测试比牌大小:炸弹----------------------------',$obj->checkCardSize(array(8,24,40,56), array(7,23,39,55)));
var_dump('测试比牌大小:火箭----------------------------',$obj->checkCardSize(array(78,79), array(7,23,39,55)));

$my_card =  array(33,1,34,18,19,35,3,36,5,38,22,58,59,60,61,45);
var_dump('我的牌：',$obj->crateCard($my_card));
var_dump('测试是否可以出牌:单张----------------------------',$obj->isPlayCard($my_card, array(12)));
var_dump('测试是否可以出牌:对子----------------------------',$obj->isPlayCard($my_card, array(12, 28)));
var_dump('测试是否可以出牌:三张----------------------------',$obj->isPlayCard($my_card, array(2, 18, 34)));
var_dump('测试是否可以出牌:三带一----------------------------',$obj->isPlayCard($my_card, array(2, 18, 34, 1)));
var_dump('测试是否可以出牌:三带二----------------------------',$obj->isPlayCard($my_card, array(2, 18, 34, 1, 17)));
var_dump('测试是否可以出牌:顺子----------------------------',$obj->isPlayCard($my_card, array(1, 2, 3, 4, 5)));
var_dump('测试是否可以出牌:连对----------------------------',$obj->isPlayCard(array(4,20,5,21,6,22,7,23), array(1,17,2,18,3,19)));
var_dump('测试是否可以出牌:飞机不带----------------------------',$obj->isPlayCard(array(4,20,36,5,21,37,8,9,10), array(1,17,33,2,18,34)));
var_dump('测试是否可以出牌:飞机带单----------------------------',$obj->isPlayCard(array(4,20,36,5,21,37,6,22,38,7,8,11,12,13), array(1,17,33,2,18,34,3,19,35,9,10,27)));
var_dump('测试是否可以出牌:飞机带双----------------------------',$obj->isPlayCard(array(4,20,36,5,21,37,7,8,24,11,27), array(1,17,33,2,18,34,9,25,10,26)));
*/
