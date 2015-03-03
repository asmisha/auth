<?php

namespace Hostel\MainBundle\Services;


use Hostel\MainBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContext;

class UserSubscriber implements EventSubscriber {
	/** @var \Symfony\Component\DependencyInjection\Container $container */
	private $container;

	public function __construct(Container $container){
		$this->container = $container;
	}

	public function getSubscribedEvents()
	{
		return array(
			'prePersist',
			'preUpdate',
		);
	}

	public function prePersist(LifecycleEventArgs $args) {
		$entity = $args->getEntity();

		if($entity instanceof User) {
			$this->userChanged($entity);
		}
	}

	public function preUpdate(LifecycleEventArgs $args){
		$entity = $args->getEntity();

		if($entity instanceof User) {
			$this->userChanged($entity);
		}
	}

	private function userChanged(User $user){
		if($user->getEmail() && !$user->getUsername()){
			$user
				->setUsername($user->getEmail())
				->setUsernameCanonical($user->getEmail())
			;
		}

		/** @var UserManager $userManager */
		$userManager = $this->container->get('fos_user.user_manager');
		$userManager->updatePassword($user);

		$isAdmin = $user->getIsAdmin();
		$user->setIsAdmin(false);
		foreach($user->getRoles() as $r){
			if(strpos($r, 'ADMIN') !== false){
				$user->setIsAdmin(true);
				if(!$isAdmin){
					$user->setShowOnMainPage(true);
				}

				break;
			}
		}
	}

}