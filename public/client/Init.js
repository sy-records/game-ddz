 /**
  * 初始化类，websock服务器类
  */
      
 var Init = {
	ws : null,
	url : "",
    timer : 0,
    reback_times : 100,   //断线重连次数
    dubug : true,
    			
	//启动websocket
	webSock: function (url) {
		this.url = url;
		ws =  new WebSocket(url);  
		ws.binaryType  = "arraybuffer"; //设置为2进制类型  webSocket.binaryType = "blob" ;
		var obj = this;
		//连接回调
		ws.onopen  = function(evt) {
            Req.heartBeat(obj);
            //清除定时器
            clearInterval(obj.timer);
			//获取用户状态         
			obj.log('系统提示: 连接服务器成功');
		};
        
		//消息回调
		ws.onmessage = function(evt) {
			if(!evt.data) return ;                   
            var total_data = new DataView(evt.data);
            var total_len = total_data.byteLength;
            if(total_data.byteLength < 4){
				obj.log('系统提示: 数据格式有问题');
				ws.close(); 
				return ;
			} 
            
            //进行粘包处理                                        
            var off = 0;                                       
            var guid = body = '';                    
            while(total_len > off) {
                var len = total_data.getUint32(off);
                var data = evt.data.slice(off, off + len + 4);
                //解析body
                body = Packet.msgunpack(data);
                //转发响应的请求
                obj.recvCmd(body);                      
                off += len + 4;
            }
            
		};
		//关闭回调
		ws.onclose = function(evt) {
            //断线重新连接
            obj.timer = setInterval(function () { 
                if(obj.reback_times == 0) {
                    clearInterval(obj.timer);
                    clearInterval(Req.timer); 
                }  else {
                    obj.reback_times--;         
                    obj.webSock(obj.url);
                }                             
            },5000);					
			obj.log('系统提示: 连接断开');
		};       
		//socket错误回调
		ws.onerror  = function(evt) {
			obj.log('系统提示: 服务器错误'+evt.type);
		};
		this.ws = ws;
		return this;
	},
    
    //处理消息回调命令字
    recvCmd: function(body) {
	    console.log('debub data'+body);
        console.log(body);
        var len = body['len'];
        var cmd = body['cmd'];
        var scmd = body['scmd'];
        var data = body['data']; 
        this.log('websocket Recv <<<  len='+len+"  cmd="+cmd+"  scmd="+scmd); 
        //路由到处理地方                               
        var func = Route[cmd][scmd];
        var str = 'Resp.'+func;
        if(func) {
			if(typeof(eval(str)) == 'function') {
				eval("Resp."+func+"(data)");
			} else {
                document.getElementById('msgText').innerHTML  += func+':'+JSON.stringify(data) + '\n';
            }
        } else {
            this.log('func is valid'); 
        }
        
        this.log("websocket Recv body <<<   func="+func);
        this.log(body); 
    },
    
	//打印日志方法
	log: function(msg) {  
		if(this.dubug) { 
		  console.log(msg);
        } 
	},
    
    //发送数据
    send: function(data, cmd, scmd) {
        //this.ws.close();
        this.log("websocket Send >>>  cmd="+cmd+"  scmd="+scmd+"  data=");
        this.log(data);
        var pack_data = Packet.msgpack(data, cmd, scmd);
        //组装数据
        if(this.ws.readyState == this.ws.OPEN) {
            this.ws.send(pack_data);
        }
    }
}