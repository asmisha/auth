#!/usr/bin/php -q
<?php

error_reporting(E_ALL);
set_time_limit(0);

function writeLog($s){
	$s = sprintf("%s: %s\n", date('r'), $s);
//	echo $s;
}

class Server{
	private $address, $port, $msgsock, $sock;

	private function listen(){
//		$this->disconnect();

		if($this->sock === null){
			if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
				echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()) . "\n";
				return false;
			}
	
			if (socket_bind($sock, $this->address, $this->port) === false) {
				echo "Не удалось выполнить socket_bind(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
				return false;
			}
	
			if (socket_listen($sock, 5) === false) {
				echo "Не удалось выполнить socket_listen(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
				return false;
			}
			$this->sock = $sock;
		}

		if (($msgsock = socket_accept($this->sock)) === false) {
			echo "Не удалось выполнить socket_accept(): причина: " . socket_strerror(socket_last_error($this->sock)) . "\n";
			return false;
		}

		$this->msgsock = $msgsock;

		return true;
	}

	public function read(){
		while(true) {
			if (!$this->msgsock && !$this->listen()) {
				return null;
			}

			do {
				if (false === ($buf = @socket_read($this->msgsock, 2048, PHP_NORMAL_READ))) {
//					echo "Не удалось выполнить socket_read(): причина: " . socket_strerror(socket_last_error($this->msgsock)) . "\n";
					socket_close($this->msgsock);
					$this->msgsock = null;
					break;
				}
				if (!$buf = trim($buf)) {
					continue;
				}
				echo ">$buf\n";
				return json_decode($buf);
			} while (true);
		}

		return null;
	}

	public function write($data){
		$data = json_encode($data);
		echo "<$data\n";
		return socket_write($this->msgsock, $data, strlen($data));
	}

	private function disconnect(){
		try {
			if($this->msgsock !== null) {
				socket_close($this->msgsock);
			}
			if($this->sock !== null) {
				socket_close($this->sock);
			}
		}catch(Exception $e){}
	}

	function __construct($address, $port){
		$address = gethostbyname($address);
		$this->address = $address;
		$this->port = $port;
	}

	function __destruct(){
		$this->disconnect();
	}
}

function getMac($host){
	$output = array();
	exec("arp -a -n | grep '${host}[^0-9]' | awk '{ print $4 }'", $output);
	$mac = @$output[0];
	return $mac;
}

function banIpMac($ip, $mac){
	writeLog(sprintf('Banning %s %s', $ip, $mac));

	exec(sprintf('sudo /srv/scripts/user_control.py deny %s %s', $ip, $mac));
}

function unbanIpMac($ip, $mac){
	writeLog(sprintf('Unbanning %s %s', $ip, $mac));

	exec(sprintf('sudo /srv/scripts/user_control.py allow %s %s', $ip, $mac));
}

function listRules(){
	writeLog(sprintf('Listing rules'));
	exec(sprintf('sudo /srv/scripts/user_control.py list_unbanned'), $out);
	if(count($out)){
		return array_combine($out, array_fill(0, count($out), 0));
	}else{
		return array();
	}
}


$s = new Server('0.0.0.0', '10001');
while($msg = $s->read()){
	switch($msg->method){
		case 'getMac':
			$s->write(getMac($msg->ip));
			break;
		case 'ban':
			banIpMac($msg->ip, $msg->mac);
			break;
		case 'unban':
			unbanIpMac($msg->ip, $msg->mac);
			break;
		case 'listRules':
			$s->write(listRules());
			break;
	}
}

?>
