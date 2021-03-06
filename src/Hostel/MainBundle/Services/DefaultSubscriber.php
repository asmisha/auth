<?php
/**
 * User: asmisha
 * Date: 10.05.14
 * Time: 13:07
 */

namespace Hostel\MainBundle\Services;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Hostel\MainBundle\Entity\Ticket;
use Hostel\MainBundle\Entity\Comment;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultSubscriber implements EventSubscriber {
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
		$em = $args->getEntityManager();

		if($entity instanceof Ticket) {
			$entity
				->setDate(new \DateTime())
				->setStatus(Ticket::STATUS_NEW)
				->setUser($this->container->get('security.context')->getToken()->getUser())
			;
		}elseif($entity instanceof Comment) {
			/** @var Comment $entity */
			$entity
				->setDate(new \DateTime())
				->setUser($this->container->get('security.context')->getToken()->getUser())
			;
		}
	}

} 