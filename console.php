<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1','6379');
	$redis->auth('root'); #password
} catch(RedisException $e) {
    exit('Connect error');
}

$commands_array = array('add','del','show');
$ttl = 3600;

$command = isset($argv[1]) ? $argv[1] : '' ;

if(in_array($command, $commands_array)){
	$key = isset($argv[2]) ? $argv[2] : '' ;
	$value = isset($argv[3]) ? $argv[3] : '' ;
	switch($command){
		case 'add':
			$redis->set($key, $value);
			$redis->expire($key, $ttl);
			echo "Add: ".$key." - ".$value."\n";
		break;
		case 'del':
			echo "Delete: key ".$key."\n";
			$redis->del($key);
		break;
		case 'show':
			if($redis->get($key) == false){
				echo "NotFound this KEY \n";
			}else{
				echo "Show key: ".$key." Value: ".$redis->get($key)."\n";
				echo "TTL ".$redis->ttl($key)."\n";
			}
		break;
		default:
			echo "No command list";
			break;
	}
}else{
	echo "Send Command (add/del/show)";
}
?>