<?php

namespace Hostel\MainBundle\Services;

use Monolog\Logger;

class RemoteIpMac implements IpMacInterface{
	private $address, $port, $sock;
	/** @var Logger */
	private $loggerBan;
	private $hostelRegex;

	private function connect(){
		$this->disconnect();

		$sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($sock === false) {
			echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()) . "\n";
			return false;
		}

		$result = @socket_connect($sock, $this->address, $this->port);
		if ($result === false) {
			echo "Не удалось выполнить socket_connect().\nПричина: ($result) " . socket_strerror(socket_last_error($sock)) . "\n";
			return false;
		}

		$this->sock = $sock;

		return true;
	}

	private function read(){
		if(!$this->sock && !$this->connect()){
			return null;
		}

		return json_decode(socket_read($this->sock, 200000), true);
	}

	private function write($data){
		if(!$this->sock && !$this->connect()){
			return null;
		}

		$data = json_encode($data);
		$data .= "\r\n";
		return socket_write($this->sock, $data, strlen($data));
	}

	private function disconnect(){
		try {
			if($this->sock !== null) {
				@socket_close($this->sock);
				$this->sock = null;
			}
		}catch(\Exception $e){}
	}

	function __construct($loggerBan, $address, $port, $hostelRegex){
		$this->loggerBan = $loggerBan;
		$address = gethostbyname($address);
		$this->address = $address;
		$this->port = $port;
		$this->hostelRegex = $hostelRegex;
	}

	public function matchIp($ip){
		return preg_match($this->hostelRegex, $ip);
	}

	function __destruct(){
		$this->disconnect();
	}

	public function getMacByIp($ip){
		$this->disconnect();

		$this->write(array(
			'method' => 'getMac',
			'ip' => $ip,
		));

		$mac = $this->read();

		$this->loggerBan->info(sprintf('Getting mac by ip %s: %s', $ip, $mac));

		return $mac;
	}

	public function banIpMac($ip, $mac){
		$this->loggerBan->info(sprintf('Banning %s %s', $ip, $mac));

		$this->disconnect();

		$this->write(array(
			'method' => 'ban',
			'ip' => $ip,
			'mac' => $mac,
		));
	}

	public function unbanIpMac($ip, $mac){
		$this->loggerBan->info(sprintf('Unbanning %s %s', $ip, $mac));

		$this->disconnect();

		$this->write(array(
			'method' => 'unban',
			'ip' => $ip,
			'mac' => $mac,
		));
	}

	public function listRules(){
		$this->disconnect();

		$this->write(array(
			'method' => 'listRules',
		));

		return $this->read();
	}
}
