<?php
/**
 * User: asmisha
 * Date: 13.12.14
 * Time: 17:12
 */

namespace Hostel\MainBundle\Services;


use Hostel\MainBundle\Entity\User;
use Monolog\Logger;

class IpMac{
	/** @var Logger */
	private $loggerBan;
	/** @var IpMacInterface[] */
	private $clients;

	function __construct($loggerBan, $clients)
	{
		$this->loggerBan = $loggerBan;
		$this->clients = $clients;
	}

	public function getMac(User $u){
		return @$this->clients[$u->getHostel()]->getMacByIp($u->getIp());
	}

	public function ban(User $u){
		$this->loggerBan->info(sprintf('Banning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		@$this->clients[$u->getHostel()]->banIpMac($u->getIp(), $u->getMac());
	}

	public function unban(User $u){
		$this->loggerBan->info(sprintf('Unbanning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		@$this->clients[$u->getHostel()]->unbanIpMac($u->getIp(), $u->getMac());
	}

	public function banIpMac($hostel, $ip, $mac){
		@$this->clients[$hostel]->unbanIpMac($ip, $mac);
	}

	public function listRules($hostel){
		return @$this->clients[$hostel]->listRules();
	}
} 