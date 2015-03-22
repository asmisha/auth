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
	
	const MAC_REGEX = '#^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$#';

	function __construct($loggerBan, $clients)
	{
		$this->loggerBan = $loggerBan;
		$this->clients = $clients;
	}

	public function getMac(User $u)
	{
		if(isset($this->clients[$u->getHostel()])){
			return $this->clients[$u->getHostel()]->getMacByIp($u->getIp());
		}else{
			return null;
		}
	}

	public function ban(User $u){
		if(!preg_match(self::MAC_REGEX, $u->getMac())){
			$this->loggerBan->info(sprintf('Updating mac "%s" for user %d', $u->getMac(), $u));
			$u->setMac($this->getMac($u));
		}
		$this->loggerBan->info(sprintf('Banning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		if(isset($this->clients[$u->getHostel()])){
			$this->clients[$u->getHostel()]->banIpMac($u->getIp(), $u->getMac());
		}
	}

	public function unban(User $u){
		if(!preg_match(self::MAC_REGEX, $u->getMac())){
			$this->loggerBan->info(sprintf('Updating mac "%s" for user %d', $u->getMac(), $u));
			$u->setMac($this->getMac($u));
		}
		$this->loggerBan->info(sprintf('Unbanning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		if(isset($this->clients[$u->getHostel()])){
			$this->clients[$u->getHostel()]->unbanIpMac($u->getIp(), $u->getMac());
		}
	}

	public function banIpMac($hostel, $ip, $mac){
		if(isset($this->clients[$hostel])){
			$this->clients[$hostel]->banIpMac($ip, $mac);
		}
	}

	public function listRules($hostel){
		if(isset($this->clients[$hostel])){
			return $this->clients[$hostel]->listRules();
		}else{
			return array();
		}
	}
} 