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
			->where('u.banned = true AND p.id IS NOT NULL AND u.connectionPayed = true')
			->orWhere('u.banned = false AND (p.id IS NULL OR u.connectionPayed = false)')
			->andWhere('u.mac is not null')
			->andWhere('u.ip is not null')
			->getQuery()
			->getResult()
		;

		foreach($users as $u){
			if($u->getBanned()){
				$this->unban($u);
			}else{
				$this->ban($u);
			}

			$u->setBanned(!$u->getBanned());
		}

		$em->flush();
	}

	private function ban(User $u){
		/** @var Logger $logger */
		$logger = $this->getContainer()->get('monolog.logger.ban');
		$logger->info(sprintf('Banning user %d %s %s', $u->getId(), $u->getFirstname(), $u->getLastname()));

		exec(sprintf('iptables -D allowed_users -s %s/32 -m mac --mac-source %s -j ACCEPT', $u->getIp(), $u->getMac()));
		//exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s,%s', $u->getIp(), $u->getMac()));
	}

	private function unban(User $u){
		/** @var Logger $logger */
		$logger = $this->getContainer()->get('monolog.logger.ban');
		$logger->info(sprintf('Unbanning user %d %s %s', $u->getId(), $u->getFirstname(), $u->getLastname()));

		exec(sprintf('iptables -A allowed_users -s %s/32 -m mac --mac-source %s -j ACCEPT', $u->getIp(), $u->getMac()));
		//exec(sprintf('sudo /usr/sbin/ipset -A ipmacs %s,%s', $u->getIp(), $u->getMac()));
	}
}