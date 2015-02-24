<?php

namespace Hostel\MainBundle\Services;

interface IpMacInterface
{
	public function getMacByIp($host);

	public function banIpMac($ip, $mac, $log = true);

	public function unbanIpMac($ip, $mac, $log = true);

	/**
	 * Returns array [ip,mac => 0]
	 * @return array
	 */
	public function listRules();
}
