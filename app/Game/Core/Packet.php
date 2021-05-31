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
 * 解包，压缩包处理逻辑.
 */
class Packet
{
    /**
     * 格式化数据.
     * @param mixed $msg
     * @param mixed $code
     * @param mixed $data
     */
    public static function packFormat($msg = 'OK', $code = 0, $data = [])
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }

    /**
     * 打包数据，固定包头，4个字节为包头（里面存了包体长度），包体前2个字节为.
     * @param mixed $data
     * @param mixed $cmd
     * @param mixed $scmd
     * @param mixed $format
     * @param mixed $type
     */
    public static function packEncode($data, $cmd = 1, $scmd = 1, $format = 'msgpack', $type = 'tcp')
    {
        if ($type == 'tcp') {
            if ($format == 'msgpack') {
                $sendStr = msgpack_pack($data);
            } else {
                $sendStr = $data;
            }
            return pack('N', strlen($sendStr) + 2) . pack('C2', $cmd, $scmd) . $sendStr;
        }
        return self::packFormat('packet type wrong', 100006);
    }

    /**
     * 解包数据.
     * @param mixed $str
     * @param mixed $format
     */
    public static function packDecode($str, $format = 'msgpack')
    {
        $header = substr($str, 0, 4);
        if (strlen($header) != 4) {
            return self::packFormat('packet length invalid', 100007);
        }
        $len = unpack('Nlen', $header);
        $len = $len['len'];
        $result = substr($str, 6);
        if ($len != strlen($result) + 2) {
            //结果长度不对
            return self::packFormat('packet length invalid', 100007);
        }

        if ($format == 'msgpack') {
            $result = msgpack_unpack($result);
        }
        if (empty($result)) {
            //结果长度不对
            return self::packFormat('packet data is empty', 100008);
        }
        $cmd = unpack('Ccmd/Cscmd', substr($str, 4, 6));
        $result = self::packFormat('OK', 0, $result);
        $result['cmd'] = $cmd['cmd'];
        $result['scmd'] = $cmd['scmd'];
        $result['len'] = $len + 4;
        return $result;
    }
}
