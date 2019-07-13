<?php

$server = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);  // 购买电话机
socket_bind($server,'127.0.0.1',9090);  // 绑定电话机
socket_listen($server,5);   // 开机

//定义一个数组
$allSockets = [$server];
while(true){
    $copySockets = $allSockets;
    if(socket_select($copySockets,$write,$except,0) === false){
        exit('error');
    }

    if(in_array($server,$copySockets)){
        $client = socket_accept($server);   //接收客户端连接
        $buf = socket_read($client,8024);   //一次读取数据的长度
        //echo $buf;

        if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/i",$buf,$matches)){
            $key = base64_encode(sha1($matches[1].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11',true));
            $res = 'HTTP/1.1 101 Switching Protocol'.PHP_EOL
                .'Upgrade: Websocket'.PHP_EOL
                .'Connection: Upgrade'.PHP_EOL
                .'WebSoket-Location: ws://127.0.0.1:9090'.PHP_EOL
                .'Sec-WebSocket-Accept:'.$key.PHP_EOL.PHP_EOL;

            //socket回复
            socket_write($client,$res,strlen($res)); //握手成功
            socket_write($client,buildMsg("hello websocket")); //注意此处要双引号

            //把webSocket客户端的socket保存起来
            $allSockets[] = $client;
        }

        //把服务端的socket移除
        $k = array_search($server,$copySockets);
        unset($copySockets[$k]);
   }

    foreach($copySockets as $s){
        $buf = socket_read($s,8024);
        if(strlen($buf) < 9){ //意味着客户端主动关闭了链接
            $k = array_search($s,$allSockets);
            unset($allSockets[$k]); //数组中删除该socket

            //服务端也要关掉
            socket_close($s);
            continue;
        }

        echo getMsg($buf); //获取客户端消息并转码
        echo PHP_EOL;
    }

}

//关机
socket_close($server);



///////////////功能函数//////////////////////////

/**
 * 编码发送给客户端的数据
 * @param $msg要处理的数据内容
 */
function buildMsg($msg){
    $frame = [];
    $frame[0] = '81';
    $len = strlen($msg);
    if($len < 126){
        $frame[1] = $len < 16 ? '0'.dechex($len) : dechex($len);
    }elseif($len < 65025){
        $s = dechex($len);
        $frame[1] = '7e'.str_repeat('0',4-strlen($s)).$s;
    }else{
        $s = dechex($len);
        $frame[1] = '7f'.str_repeat('0',16-strlen($s)).$s;
    }

    $data = '';
    $l = strlen($msg);
    for($i=0;$i<$l;$i++){
        $data .= dechex(ord($msg{$i}));
    }
    $frame[2] = $data;
    $data = implode('',$frame);
    return pack('H*',$data);
}

/**
 * 解析客户端发送过来的数据
 * @param $buffer
 */
function getMsg($buffer){
    $res = '';
    $len = ord($buffer)&127;
    if($len === 126){
        $masks = substr($buffer,4,4);
        $data = substr($buffer,8);
    }elseif($len === 127){
        $masks = substr($buffer,10,4);
        $data = substr($buffer,14);
    }else{
        $masks = substr($buffer,2,4);
        $data = substr($buffer,6);
    }

    for($index=0;$index<strlen($data);$index++){
        $res .= $data[$index]^$masks[$index % 4];
    }

    return $res;
}