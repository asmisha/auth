<?php

namespace Hostel\MainBundle\Services;

use Monolog\Logger;

class LocalIpMac implements IpMacInterface{
	/** @var Logger */
	private $loggerBan;

	function __construct($loggerBan)
	{
		$this->loggerBan = $loggerBan;
	}

	public function getMacByIp($host){
		$output = array();
		exec("/usr/sbin/arp -a -n | grep '${host}[^0-9]' | awk '{ print $4 }'", $output);
		$mac = @$output[0];
		return $mac;
	}

	public function banIpMac($ip, $mac, $log = true){
		if($log){
			$this->loggerBan->info(sprintf('Banning %s %s', $ip, $mac));
		}

		exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s,%s', $ip, $mac));
	}

	public function unbanIpMac($ip, $mac, $log = true){
		if($log){
			$this->loggerBan->info(sprintf('Unbanning %s %s', $ip, $mac));
		}

		exec(sprintf('sudo /usr/sbin/ipset -A ipmacs %s,%s', $ip, $mac));
	}

	public function listRules(){
		exec('ipset -L | grep ","', $out);
		if(count($out)){
			return array_combine($out, array_fill(0, count($out), 0));
		}else{
			return array();
		}
	}

} 