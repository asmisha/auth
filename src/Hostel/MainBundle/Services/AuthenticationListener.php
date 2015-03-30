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
			$oldIp = $user->getIp();
			$oldMac = $user->getMac();

			$ip = $this->requestStack->getCurrentRequest()->getClientIp();
			$user->setIp($ip ?: $oldIp);

			$mac = $this->ipmac->getMac($user);
			$user->setMac($mac ?: $oldMac);

			// If user is not banned and something changed - ban old ip-mac and unban new one
			if(!$user->getBanned() && ($oldIp != $ip || $oldMac != $mac)){
				$this->ipmac->banIpMac($oldIp, $oldMac);
				$this->ipmac->unban($user);
			}

			$this->em->persist($user);
			$this->em->flush();
		}
	}
}