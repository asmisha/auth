<?php
namespace Hostel\MainBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\Payment;
use Hostel\MainBundle\Entity\PaymentRequest;
use Hostel\MainBundle\Entity\User;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('migrate')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var Connection $db */
		$db = $this->getContainer()->get('doctrine')->getConnection('old');

		$users = $db->fetchAll('SELECT * FROM hna_users u JOIN hna_pays p ON p.user_id = u.user_id');

		$a = array();
		foreach($users as $u){
			$sum = 0;
			for($i = 1; $i <= 12; $i++){
				$sum += $u[$i];
			}
			if(!$sum){
				continue;
			}

			$user = new User();
			$user->setFirstname($u['firstname']);
			$user->setMiddlename($u['lastname']);
			$user->setLastname($u['surname']);
			$user->setUsername($u['login']);
			$user->setUsernameCanonical($u['login']);
			$user->setPlainPassword($u['pass']);
			$user->setGroupNumber($u['group']);
			$user->setRoom($u['block'].$u['room']);
			$user->setCreatedAt(new \DateTime($u['register']));
			$user->setHostel(4);
			$user->setConnectionPayed($u['connect']);

			for($i = 1; $i <= 12; $i++){
				if($u[$i]){
					$p = new Payment();
					$p
						->setMonth($i)
						->setYear(2014 + ($i < 9))
						->setAmount($this->getContainer()->getParameter('cost_monthly'))
						->setUser($user)
					;
					$user->addPayment($p);

					$em->persist($p);
				}
			}

			$em->persist($user);
			$a[$u['contract']] = $user;
		}

		$em->flush();

		$db = $this->getContainer()->get('doctrine')->getConnection();
		foreach($a as $k=>$i){
			$db->executeUpdate("UPDATE User SET id = $k WHERE id = {$i->getId()}");
		}
	}
}