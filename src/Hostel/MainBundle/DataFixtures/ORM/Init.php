<?php
/**
 * User: asmisha
 * Date: 15.03.14
 * Time: 21:32
 */

namespace Hostel\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Hostel\MainBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Doctrine\UserManager;

class Init implements FixtureInterface, ContainerAwareInterface{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		/** @var UserManager $userManager */
		$userManager = $this->container->get('fos_user.user_manager');
		//make some users
		/** @var User $user */
		$user = $userManager->createUser();
		$user
			->setUsername('admin')
			->setPlainPassword('admin')
			->setEmail('asmisha@tut.by')
			->setEnabled(true)
			->addRole('ROLE_SUPER_ADMIN')
			->setSuperAdmin(true)
			->setIsAdmin(true)
			->setMiddlename('')
			->setRoom('')
			->setGroupNumber('')
		;

		$user->setFirstname('');
		$user->setLastname('');

		$userManager->updateUser($user);
	}
} 