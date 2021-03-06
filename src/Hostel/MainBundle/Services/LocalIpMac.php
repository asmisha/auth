<?php

namespace Hostel\MainBundle\Services;

use Monolog\Logger;

class LocalIpMac implements IpMacInterface{
	/** @var Logger */
	private $loggerBan;
	private $hostelRegex;

	function __construct($loggerBan, $hostelRegex)
	{
		$this->loggerBan = $loggerBan;
		$this->hostelRegex = $hostelRegex;
	}

	public function matchIp($ip){
		return preg_match($this->hostelRegex, $ip);
	}

	public function getMacByIp($host){
		$output = array();
		exec("/usr/sbin/arp -a -n | grep '${host}[^0-9]' | awk '{ print $4 }'", $output);
		$mac = @$output[0];
		return $mac;
	}

	public function banIpMac($ip, $mac){
		$this->loggerBan->info(sprintf('Banning %s %s', $ip, $mac));

		exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s,%s', $ip, $mac));
	}

	public function unbanIpMac($ip, $mac){
		$this->loggerBan->info(sprintf('Unbanning %s %s', $ip, $mac));

		exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s', $ip));
		exec(sprintf('sudo /usr/sbin/ipset -A ipmacs %s,%s', $ip, $mac));
	}

	public function listRules(){
		exec('/usr/sbin/ipset -L | grep ","', $out);
		if(count($out)){
			return array_combine($out, array_fill(0, count($out), 0));
		}else{
			return array();
		}
	}

} 