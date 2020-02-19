/**响应服务器命令字处理类*/

var Resp = {	
	//心跳响应
    heartAsk: function(data) {
        this.log(data);
		this.showTips('心跳数据:');
    },

	//登录失败
    loginFail: function(data) {
        this.log(data);
		this.showTips('登录失败:');
        //跳转登陆页面
        document.location.href = "login";
    },

	//登录成功响应
	loginSuccess: function(data) {
        this.log(data);
		this.showTips('登录成功');
		//如果用户在房间里, 直接进入房间
		if(data.is_room == 1) {
			//进入房间请求
			Req.GameStart(obj,{});
		}
    },

	//游戏开始响应
    gameStart: function(data) {
		this.log(data);
		this.showTips('游戏开始');
    },

    //用户信息
    userInfo: function(data) {
        this.log(data);
		this.showTips('用户信息');
    },
	
	//叫地主
    gameCall: function(data) {
        this.log(data);
		this.showTips('我叫地主成功');
		document.getElementById('call').disabled = true;
		document.getElementById('nocall').disabled = true;
    },

	//叫地主广播
    gameCallTips: function(data) {
        this.log(data);
		if(data.calltype == 1) {
			var tips = data.account+'叫地主';
		} else {
			var tips = data.account+'不叫';
		}
		this.showTips('广播:'+tips);
    },

	//摸底牌
    gameCatchCardTips: function(data) {
        this.log(data);
		this.showTips('广播:'+data.user+'摸底牌'+data.hand_card);
		//摸完底牌, 重新构造牌, 这里偷懒, 重新触发开始游戏
		Req.GameStart(obj,{});
	},


	//聊天数据
    chatMsg: function(data) {
        this.log(data);
		// this.showTips('聊天内容是:'+JSON.stringify(data));
		this.showTips(data.user + '发送的聊天内容是：' + data.data);
    },
	
	//进入房间失败
	enterRoomFail: function(data) {
        this.log(data);
		this.showTips('进入房间失败:'+data.msg);
    },
	
	//进入房间成功,解锁按钮
    enterRoomSucc: function(data) {
		this.log(data);
		this.showTips('进入房间成功:'+JSON.stringify(data));
		var card = data.card;
		var chair_id = data.chair_id;
		var is_master = data.is_master;
		var is_game_over =  data.is_game_over;
		info = data
		if(typeof(data.calltype) == 'undefined') {
			document.getElementById('call').disabled = false;
			document.getElementById('nocall').disabled = false;
		} else {
			document.getElementById('call').disabled = true;
			document.getElementById('nocall').disabled = true;
		}

		//显示牌
		if(card && chair_id) {
			//循环展现牌
			var show_card = '';
			for(var k in card) {
				show_card += '<span style="border:1px solid black;width:36px;height:56px;margin:2px;padding:3px;display:block;float:left"><input type="checkbox" name="handcard" value="'+card[k]+'">'+this.getCard(card[k])+'</span>';
			}
			var id = 'chair_'+chair_id;
			document.getElementById(id).innerHTML  =  show_card;
		}

		//是否为地主
		if(is_master == 1) {
			if(typeof(data.master) != 'undefined') {
				document.getElementById('master').innerHTML  =  '(地主)-'+chair_id+'号位置';
			} else {
				document.getElementById('master').innerHTML  =  '(农民)-'+chair_id+'号位置';
			}
		}

		//判断游戏是否结束
		if(is_game_over) {
			this.showTips('游戏结束');
		} else {			
			//轮到谁出来, 就解锁谁的按钮
			if(typeof(data.index_chair_id) != 'undefined' && info.chair_id == data.index_chair_id) {
				//解锁打牌按钮
				document.getElementById('play').disabled = false;
				document.getElementById('pass').disabled = false;
				var tips = data.is_first_round ? '请首次出牌' : '请跟牌';
				this.showTips(tips);
			} else {
				document.getElementById('play').disabled = true;
				document.getElementById('pass').disabled = true;
			}
		}
    },
	
	//出牌提示
	gameOutCard: function(data) {
        this.log(data);
		this.showTips('出牌提示:'+data.msg);
		if(data.status == 0) {
			//移除当前牌元素
			var obj_box = document.getElementsByName("handcard");
			var obj_item = [];
			for(k in obj_box){
				if(obj_box[k].checked){
					obj_item[k] = obj_box[k].parentNode;	
				}
			}
			//循环删除
			for(k in obj_item){
				obj_item[k].remove(this);
			}
		}
    },
	
	//出牌广播响应
	gameOutCardResp: function(data) {
		//判断游戏是否结束
		if(data.is_game_over) {
			this.showTips('广播:游戏结束,'+data.account+'胜利, 请点击"开始游戏",进行下一轮游戏');
			//手牌重置
			document.getElementById('chair_1').innerHTML  =  '';
			document.getElementById('chair_2').innerHTML  =  '';
			document.getElementById('chair_3').innerHTML  =  '';
			document.getElementById('last_card').innerHTML = '';
			document.getElementById('out_card').innerHTML = '';
			document.getElementById('play').disabled = true;
			document.getElementById('pass').disabled = true;
		} else {
			this.log(data);
			var play = data.show_type == 1 ? '跟牌' : '过牌';
			if(data.last_card == null || data.last_card.length < 1) {
				play = '出牌';
			} 
			this.showTips('广播: 第'+data.round+'回合,第'+data.hand_num+'手出牌, '+data.account+play+', 上次牌值是'+data.last_card+', 本次出牌值是'+data.card+', 本次出牌型是'+data.card_type);
			this.showPlayCard(data.last_card,data.card); 

			//自己出牌按钮变灰
			if(info.chair_id == data.next_chair_id) {
				document.getElementById('play').disabled = false;
				document.getElementById('pass').disabled = false;
				//提示下一个跟牌操作
				var tips = data.is_first_round ? '请首次出牌' : '请跟牌';
				this.showTips(tips);
			} else {
				document.getElementById('play').disabled = true;
				document.getElementById('pass').disabled = true;
			}
		}

    },
	
	//广播消息响应
	broadcast: function(data) {
		this.log(data);
		this.showTips("广播:消息,"+JSON.stringify(data));
    },

	//显示打牌过程
	showPlayCard: function(last_card, out_card) {
		document.getElementById('last_card').innerHTML  = '';
		document.getElementById('out_card').innerHTML  = '';
		if(last_card != null && typeof(last_card) == 'object' && last_card.length > 0) {
			var l = '';
			for(k in last_card) {
				l += '<span style="border:1px solid black;width:36px;height:56px;margin:2px;padding:3px;display:block;float:left">'+this.getCard(last_card[k])+'</span>';
			}
			document.getElementById('last_card').innerHTML  = l;
		}
		if(out_card != null && typeof(out_card) == 'object' && out_card.length > 0) {
			var n = '';
			for(j in out_card) {
				n += '<span style="border:1px solid black;width:36px;height:56px;margin:2px;padding:3px;display:block;float:left">'+this.getCard(out_card[j])+'</span>';
			}
			document.getElementById('out_card').innerHTML  = n;
		}

	},

	//构造牌
	getCard: function(card_val) {
		var card = '';
		var color = parseInt(card_val / 16);
		if(color == CardType.HEITAO) {
			card += '♠';
		} else if(color == CardType.HONGTAO) {
			card += '<span style="color:red">♥</span>';
		} else if(color == CardType.MEIHUA) {
			card += '♣';
		} else if(color == CardType.FANGKUAI) {
			card += '<span style="color:red">♦</span>';
		} else if(color == CardType.XIAOWANG) {
			if(card_val == 78) {
				card += 's';
			} else if(card_val == 79) {
				card += '<span style="color:red">B</span>';
			}
		}

		if(card_val == 78) {
			card +='_'+CardVal.CARD_XIAOWANG;
		} else if(card_val == 79) {
			card +='_'+CardVal.CARD_DAWANG;
		} else {
			//牌值渲染
			var value = parseInt(card_val % 16);
			switch(value) {
				case 1:
					card +='_'+CardVal.CARD_SAN;
					break;
				case 2:
					card +='_'+CardVal.CARD_SI;
					break;
				case 3:
					card +='_'+CardVal.CARD_WU;
					break;
				case 4:
					card +='_'+CardVal.CARD_LIU;
					break;
				case 5:
					card +='_'+CardVal.CARD_QI;
					break;
				case 6:
					card +='_'+CardVal.CARD_BA;
					break;
				case 7:
					card +='_'+CardVal.CARD_JIU;
					break;
				case 8:
					card +='_'+CardVal.CARD_SHI;
					break;
				case 9:
					card +='_'+CardVal.CARD_J;
					break;
				case 10:
					card +='_'+CardVal.CARD_Q;
					break;
				case 11:
					card +='_'+CardVal.CARD_K;
					break;
				case 12:
					card +='_'+CardVal.CARD_A;
					break;
				case 13:
					card +='_'+CardVal.CARD_ER;
					break;
			}
		}
		return card;
	},

	//日志显示协议返回数据
	log: function(data) {
        //document.getElementById('msgText').innerHTML  += JSON.stringify(data) + '\n';
		console.log(data);
    },
	
	//显示提示语句
	showTips: function(tips) {
		document.getElementById('msgText').innerHTML  += tips + '\n';
	}
}