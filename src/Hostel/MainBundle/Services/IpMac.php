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
		$this->loggerBan->info(sprintf('Banning user %d %s %s', $u->getId(), $u->getFirstname(), $u->getLastname()));

//		exec(sprintf('iptables -D allowed_users -s %s/32 -m mac --mac-source %s -j ACCEPT', $u->getIp(), $u->getMac()));
		exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s,%s', $u->getIp(), $u->getMac()));
	}

	public function unban(User $u){
		$this->loggerBan->info(sprintf('Unbanning user %d %s %s', $u->getId(), $u->getFirstname(), $u->getLastname()));

//		exec(sprintf('iptables -A allowed_users -s %s/32 -m mac --mac-source %s -j ACCEPT', $u->getIp(), $u->getMac()));
		exec(sprintf('sudo /usr/sbin/ipset -A ipmacs %s,%s', $u->getIp(), $u->getMac()));
	}

} 