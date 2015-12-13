<?php
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    var_dump($request->fd, $request->get, $request->server);
    $start_fd = 0;
    while(true){
        $conn_list = $ws->connection_list($start_fd, 10);
        if($conn_list===false or count($conn_list) === 0){
            echo "finish\n";
            break;
        }
        $start_fd = end($conn_list);
        var_dump($conn_list);
        foreach($conn_list as $fd){
            $ws->push($fd, "消息: ".$request->fd."来到了房间");
        }
    }
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $start_fd = 0;
    while(true){
        $conn_list = $ws->connection_list($start_fd, 10);
        if($conn_list===false or count($conn_list) === 0){
            echo "finish\n";
            break;
        }
        $start_fd = end($conn_list);
        var_dump($conn_list);
        foreach($conn_list as $fd){
            $ws->push($fd, $frame->fd.": {$frame->data}");
        }
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
    $start_fd = 0;
    while(true){
        $conn_list = $ws->connection_list($start_fd, 10);
        if($conn_list===false or count($conn_list) === 0){
            echo "finish\n";
            break;
        }
        $start_fd = end($conn_list);
        var_dump($conn_list);
        foreach($conn_list as $_fd){
            $ws->push($_fd,   "消息: ".$fd."离开了房间");
        }
    }
});

$ws->start();