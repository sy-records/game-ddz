var Packet = {
    //jons数据打包成二进制数据
    encode : function(data, cmd, scmd) {
        var data = JSON.stringify(data);
        var len = data.length + 6;                             
        var buf = new ArrayBuffer(len); // 每个字符占用1个字节
        var buff_data = new DataView(buf, 0, len);
        var str_len = data.length;               
        buff_data.setUint32(0, str_len+2);
        buff_data.setUint8(4, cmd);
        buff_data.setUint8(5, scmd);                                                               
        for (var i = 0; i < str_len; i++) {
             buff_data.setInt8(i+6, data.charCodeAt(i));
        }                
        return buf;
    },
    
    //二进制数据解包成二进制数据          
    decode : function(buff) {
        var body = '';
        var len = new DataView(buff, 0, 4).getUint32(0);
        var body_data = new DataView(buff, 4, len);
        //解析cmd
        var cmd = body_data.getUint8(0);
        var scmd = body_data.getUint8(1);
                                
        //解析body
        for(var i = 2; i < body_data.byteLength; i++) {                    
            body += String.fromCharCode(body_data.getUint8(i)); 
        }              
        //console.log("data decode   >>>  len="+len+" cmd="+cmd+"  scmd="+scmd+"  data="+body); 
        var body = JSON.parse(body);
        body["cmd"] = cmd;
        body["scmd"] = scmd;
        return body;
    },
    
    encodeUTF8: function(str){
        var temp = "",rs = "";
        for( var i=0 , len = str.length; i < len; i++ ){
            temp = str.charCodeAt(i).toString(16);
            rs  += "\\u"+ new Array(5-temp.length).join("0") + temp;
        }
        return rs;
    },
    
    decodeUTF8: function(str){
        return str.replace(/(\\u)(\w{4}|\w{2})/gi, function($0,$1,$2){
            return String.fromCharCode(parseInt($2,16));
        }); 
    },
    
    //使用msgpack解包arraybuf数据
    msgunpack: function(buff) {
        var body = '';
        var len = new DataView(buff, 0, 4).getUint32(0);
        var body_data = new DataView(buff, 4, len);
        //解析cmd
        var cmd = body_data.getUint8(0);
        var scmd = body_data.getUint8(1);
                                
        //解析body
        for(var i = 2; i < body_data.byteLength; i++) {                    
            body += String.fromCharCode(body_data.getUint8(i)); 
        }              
        //console.log("data msgpack decode   >>>  cmd="+cmd+"  scmd="+scmd+"  len="+len+" data="+body);
        var body = msgpack.unpack(body);
        body["cmd"] = cmd;
        body["scmd"] = scmd;
        body["len"] = len;
        return body;      
    },
    
    //使用packmsg打包object数据对象
    msgpack: function(data, cmd, scmd) {
        //var dt = {};
        //dt.data = data;          
        var data_buff = msgpack.pack(data);
        var str_buff = String.fromCharCode.apply(null, new Uint8Array(data_buff));                 
        var len = str_buff.length + 6;                                             
        var buf = new ArrayBuffer(len); // 每个字符占用1个字节
        var buff_data = new DataView(buf, 0, len);                               
        var str_len = str_buff.length;                               
        buff_data.setUint32(0, str_len + 2);
        buff_data.setUint8(4, cmd);
        buff_data.setUint8(5, scmd);
        
        for (var i = 0; i < str_len; i++) {
             buff_data.setInt8(i+6, str_buff.charCodeAt(i));
        }
        //console.log("data msgpack encode  >>>  cmd="+cmd+"  scmd="+scmd+"  len="+len+"  data=");  
        //console.log(data);             
        return buf;      
    }                      
}