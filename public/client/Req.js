/**发请求命令字处理类*/

var Req = {
    //定时器
    timer : 0,

    //发送心跳
    heartBeat:function(obj) {
        this.timer = setInterval(function () {
            if(obj.ws.readyState == obj.ws.OPEN) {
                var data = {};
                data['time'] = (new Date()).valueOf()
                obj.send(data, MainCmd.CMD_SYS, SubCmd.HEART_ASK_REQ);
            } else {
                clearInterval(this.timer);
            }
        }, 600000);
    },

    //游戏开始
    GameStart: function(obj,data) {
        var data = {};
        obj.send(data, MainCmd.CMD_GAME, SubCmd.SUB_GAME_START_REQ);
    },

    //抢地主
    GameCall: function(obj,status) {
        var data = {"type": status};
        obj.send(data, MainCmd.CMD_GAME, SubCmd.SUB_GAME_CALL_REQ);
    },

	//玩游戏
    PlayGame: function(obj,data) {
        obj.send(data, MainCmd.CMD_GAME, SubCmd.SUB_GAME_OUT_CARD_REQ);
    },

    //聊天消息
    ChatMsg: function(obj, data) {
        var data = {data};
        obj.send(data, MainCmd.CMD_GAME, SubCmd.CHAT_MSG_REQ);
    },
}