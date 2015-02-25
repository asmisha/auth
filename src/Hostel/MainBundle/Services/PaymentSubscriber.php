<?php

namespace Hostel\MainBundle\Services;


use Hostel\MainBundle\Entity\Payment;
use Hostel\MainBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PaymentSubscriber implements EventSubscriber {
	/** @var \Symfony\Component\DependencyInjection\Container $container */
	private $container;

	public function __construct(Container $container){
		$this->container = $container;
	}

	public function getSubscribedEvents()
	{
		return array(
			'prePersist',
		);
	}

	public function prePersist(LifecycleEventArgs $args) {
		$entity = $args->getEntity();

		if($entity instanceof Payment) {
			/** @var TokenInterface $token */
			$token = $this->container->get('security.context')->getToken();
			$entity
				->setAmount($this->container->getParameter('cost_monthly'))
				->setCreatedBy($token && $token->getUser() instanceof User ? $token->getUser() : null)
			;
		}
	}

}