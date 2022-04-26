<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1','6379');
	$redis->auth('root'); #password
} catch(RedisException $e) {
    exit('Connect error');
}
$url = parse_url("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']); #or https
$dirs = explode('/', $url['path']);
isset($url['query']) && parse_str($url['query'], $_GET);

$commands_array = array('api');
$command = isset($dirs[1]) ? $dirs[1] : '' ;

if(in_array($command, $commands_array)){
    $method = isset($dirs[2]) ? $dirs[2] : '' ;
    switch($method){
            case 'redis':
                $data = [];
                $key = isset($dirs[3]) ? $dirs[3] : '' ;
                if($key != ''){
                    header('Content-Type: application/json; charset=utf-8');
                    if($redis->del($key) == '1'){
                        $data['status'] = true;
                        $data['code'] = 200;
                    }else{
                        $data['status'] = false;
                        $data['code'] = 500;
                        $data['message'] = "Error message: Wrong KEY";
                    }
                    echo json_encode($data);
                }else{
                    header('Content-Type: application/json; charset=utf-8');
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
            break;
        }
}else{
    $allKeys = $redis->keys('*');
    $i = 0;
    echo "<ul>";
    while($i < count($allKeys)){
        echo "<li id='".$allKeys[$i]."'>".$allKeys[$i]." : ".$redis->get($allKeys[$i])." <a href=/api/del/".$allKeys[$i]."/ class='remove'>delete</a></li>";
        $i++;
    }
    echo "</ul>";
}
?>