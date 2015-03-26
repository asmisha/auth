<?php
/**
 * User: asmisha
 * Date: 13.12.14
 * Time: 17:15
 */

namespace Hostel\MainBundle\Services;

use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationListener implements EventSubscriberInterface
{
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	/** @var \Symfony\Component\HttpFoundation\RequestStack  */
	private $requestStack;
	/** @var  IpMac */
	private $ipmac;

	function __construct(RequestStack $requestStack, EntityManager $em, IpMac $ipmac)
	{
		$this->requestStack = $requestStack;
		$this->em = $em;
		$this->ipmac = $ipmac;
	}


	/**
	 * getSubscribedEvents
	 *
	 * @author 	Joe Sexton <joe@webtipblog.com>
	 * @return 	array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
		);
	}

	/**
	 * onAuthenticationSuccess
	 *
	 * @author 	Joe Sexton <joe@webtipblog.com>
	 * @param 	AuthenticationEvent $event
	 */
	public function onAuthenticationSuccess( AuthenticationEvent $event )
	{
		/** @var User $user */
		$user = $event->getAuthenticationToken()->getUser();
		if($user instanceof User){
			$ip = $this->requestStack->getCurrentRequest()->getClientIp();

			if(!$ip){
				file_put_contents('/tmp/e', $user->getId().': '.var_export($_SERVER)."\n", FILE_APPEND);
			}

			$mac = $this->ipmac->getMac($user);
			file_put_contents('/tmp/f', $user->getId()."$ip $mac\n", FILE_APPEND);

			// If user is not banned and something changed - ban him
			if(!$user->getBanned() && (($ip && $ip !== $user->getIp()) || ($mac && $mac !== $user->getMac()))){
				$this->ipmac->ban($user);
				$banned = true;
			}else{
				$banned = false;
			}

			if($ip){
				$user->setIp($ip);
			}

			if($mac){
				$user->setMac($mac);
			}

			// if something changed and we banned the user - unban him with the new ip and mac info
			if($banned){
				$this->ipmac->unban($user);
			}

			$this->em->persist($user);
			$this->em->flush();
		}
	}
}