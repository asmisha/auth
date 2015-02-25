<?php

namespace Hostel\MainBundle\Services;

interface IpMacInterface
{
	public function getMacByIp($host);

	public function banIpMac($ip, $mac);

	public function unbanIpMac($ip, $mac);

	/**
	 * Returns array [ip,mac => 0]
	 * @return array
	 */
	public function listRules();
}
