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

	const MAC_REGEX = '#^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$#i';

	function __construct($loggerBan, $clients)
	{
		$this->loggerBan = $loggerBan;
		$this->clients = $clients;
	}

	/**
	 * @param $ip
	 * @return IpMacInterface|null
	 */
	private function getClient($ip){
		foreach($this->clients as $c){
			if($c->matchIp($ip)){
				return $c;
			}
		}

		return null;
	}

	public function getMac(User $u)
	{
		if($client = $this->getClient($u->getIp())){
			return $client->getMacByIp($u->getIp());
		}else{
			return null;
		}
	}

	public function ban(User $u){
		if(!preg_match(self::MAC_REGEX, $u->getMac())){
			$this->loggerBan->info(sprintf('Updating mac "%s" for user %s', $u->getMac(), $u));
			$u->setMac($this->getMac($u));
		}
		$this->loggerBan->info(sprintf('Banning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		if($client = $this->getClient($u->getIp())){
			$client->banIpMac($u->getIp(), $u->getMac());
		}
	}

	public function unban(User $u){
		if(!preg_match(self::MAC_REGEX, $u->getMac())){
			$this->loggerBan->info(sprintf('Updating mac "%s" for user %s', $u->getMac(), $u));
			$u->setMac($this->getMac($u));
		}
		$this->loggerBan->info(sprintf('Unbanning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

		if($client = $this->getClient($u->getIp())){
			$client->unbanIpMac($u->getIp(), $u->getMac());
		}
	}

	public function banIpMac($ip, $mac){
		if($client = $this->getClient($ip)){
			$client->banIpMac($ip, $mac);
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