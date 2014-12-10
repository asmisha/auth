<?php
/**
 * User: asmisha
 * Date: 10.05.14
 * Time: 13:07
 */

namespace Hostel\MainBundle\Services;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Entity\Comment;
use Hostel\MainBundle\Entity\Request;
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

		if($entity instanceof Request) {
			/** @var Request $entity */
			/** @var User[] $admins */
			$admins = $em->getRepository('HostelMainBundle:User')->findBy(array(
				'isAdmin' => true
			));
			$admin = null;

			foreach($admins as $a){
				if(preg_match("#{$a->getRoomPattern()}#", $entity->getUser()->getRoom()) || (in_array('ROLE_SUPER_ADMIN', $a->getRoles()) && !$admin)){
					$admin = $a;
				}
			}

			$entity
				->setDate(new \DateTime())
				->setStatus(Request::STATUS_NEW)
				->setAdmin($admin)
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