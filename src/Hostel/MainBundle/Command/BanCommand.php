<?php
namespace Hostel\MainBundle\Command;

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

class BanCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('cron:ban')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var User[] $users */
		$users = $em->getRepository('HostelMainBundle:User')->createQueryBuilder('u')
			->leftJoin('u.payments', 'p', 'WITH', 'p.month = :month AND p.year = :year')
			->setParameter('month', date('n'))
			->setParameter('year', date('Y'))
			->where('u.banned = true AND ((p.id IS NOT NULL AND u.connectionPayed = true) OR u.isAdmin = true)')
			->orWhere('u.banned = false AND (p.id IS NULL OR u.connectionPayed = false) AND u.isAdmin = false')
			->andWhere('u.mac is not null')
			->andWhere('u.ip is not null')
			->getQuery()
			->getResult()
		;

		foreach($users as $u){
			if($u->getBanned()){
				$this->getContainer()->get('ipmac')->unban($u);
			}else{
				$this->getContainer()->get('ipmac')->ban($u);
			}
			$u->setBanned(!$u->getBanned());
		}

		$em->flush();
	}
}