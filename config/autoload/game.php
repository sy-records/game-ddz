<?php

declare(strict_types=1);

return [
    'user_info_key' => 'user:info:%s',     //用户信息redis的key，fd对应用户信息
    'user_bind_key' => 'user:bind:%s',     //用户绑定信息和fd绑定key，里面存是根据fd存入account和fd绑定关系
    'expire' => 1 * 24 * 60 * 60,          //设置key过期时间， 设置为1天
    'room_list'=> 'user:room:list',        //用户进入房间队列
    'room_user_list'=> 'room:user:list:%s',  //用户进入房间号队列
    'user_room_no' => 'user:room:no',         //用户自增房间号
    'user_room' => 'user:room:map:%s',        //用户和房间映射关系
    'user_room_data' => 'user:room:data:%s',  //用户游戏房间数据
    'user_room_play' => 'user:room:play:%s',  //用户游戏房间打牌步骤数据
];
