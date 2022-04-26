<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1','6379');
	$redis->auth('root'); #password
} catch(RedisException $e) {
    exit('Connect error');
}
$method = $_SERVER['REQUEST_METHOD'];
$url = parse_url("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']); #or https
$dirs = explode('/', $url['path']);
isset($url['query']) && parse_str($url['query'], $_GET);
$commands_array = array('api');
$command = isset($dirs[1]) ? $dirs[1] : '' ;

if(in_array($command, $commands_array)){
    $action = isset($dirs[2]) ? $dirs[2] : '' ;
    switch($action){
        case 'redis':
            if($method == 'GET'){
                header('Content-Type: application/json; charset=utf-8');
                $key = isset($dirs[3]) ? $dirs[3] : '' ;
                $data = [];
                if($key != ''){
                    if($redis->get($key) == false){
                        $data['code'] = 499;
                        $data['message']='Key not found';
                    }else{
                        $redis->del($key);
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                    }
                    echo json_encode($data);
                }else{
                    $allKeys = $redis->keys('*');
                    $i = 0;
                    $data['counter'] = count($allKeys);
                    $data['status'] = true;
                    $data['code'] = 200;
                    while($i < count($allKeys)){
                        $data[$allKeys[$i]] = $redis->get($allKeys[$i]);
                        $i++;
                    }
                echo json_encode($data);
                }
            }
            if($method == 'DELETE'){
                $key = isset($dirs[3]) ? $dirs[3] : '' ;
                $data = [];
                header('Content-Type: application/json; charset=utf-8');
                if($redis->get($key) == false){
				    $data['status'] = false;
                    $data['code'] = 500;
                    $data['message'] = 'key not found';
                }else{
                    $allKeys = $redis->del($key);
                    $data['status'] = true;
                    $data['code'] = 200;
                }
                echo json_encode($data);
            }
        break;
    }
}else{
    $allKeys = $redis->keys('*');
    $i = 0;
    echo "<ul>";
    while($i < count($allKeys)){
        echo "<li id='".$allKeys[$i]."'>".$allKeys[$i]." : ".$redis->get($allKeys[$i])." <a href=/api/redis/".$allKeys[$i]."/ class='remove'>delete</a></li>";
        $i++;
    }
    echo "</ul>";
}

?>