<?php
/**
 * User: asmisha
 * Date: 13.12.14
 * Time: 17:12
 */

namespace Hostel\MainBundle\Services;


use Hostel\MainBundle\Entity\User;
use Monolog\Logger;

class IpMac {
	/** @var Logger */
	private $loggerBan;

	function __construct($loggerBan)
	{
		$this->loggerBan = $loggerBan;
	}

	public function getMac($host){
		$output = array();
		exec("/usr/sbin/arp -a -n | grep '${host}[^0-9]' | awk '{ print $4 }'", $output);
		$mac = @$output[0];
		return $mac;
	}

	public function ban(User $u){
		$this->loggerBan->info(sprintf('Banning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		$this->banIpMac($u->getIp(), $u->getMac(), false);
	}

	public function banIpMac($ip, $mac, $log = true){
		if($log){
			$this->loggerBan->info(sprintf('Banning %s %s', $ip, $mac));
		}

		exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s,%s', $ip, $mac));
	}

	public function unban(User $u){
		$this->loggerBan->info(sprintf('Unbanning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		$this->unbanIpMac($u->getIp(), $u->getMac(), false);
	}

	public function unbanIpMac($ip, $mac, $log = true){
		if($log){
			$this->loggerBan->info(sprintf('Unbanning %s %s', $ip, $mac));
		}

		exec(sprintf('sudo /usr/sbin/ipset -A ipmacs %s,%s', $ip, $mac));
	}

	/**
	 * Returns array [ip,mac => 0]
	 * @return array
	 */
	public function listRules(){
		exec('ipset -L | grep ","', $out);
		return array_combine($out, array_fill(0, count($out), 0));
	}

} 