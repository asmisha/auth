<?php
namespace Hostel\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\Payment;
use Hostel\MainBundle\Entity\PaymentRequest;
use Hostel\MainBundle\Entity\User;
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
		$users = $em->getRepository('HostelMainBundle:User')->findAll();

		foreach($users as $u){
			if(!$u->getMac())
				continue;

			$monthPayed = false;

			foreach($u->getPayments() as $payment){
				if($payment->getMonth() == date('n') && $payment->getYear() == date('Y')){
					$monthPayed = true;
					break;
				}
			}

			if($u->getConnectionPayed() && $monthPayed){
				$this->ban($u);
			}else{
				$this->unban($u);
			}
		}
	}

	private function ban(User $u){
		exec(sprintf('sudo /usr/sbin/ipset -D ipmacs %s,%s', $u->getIp(), $u->getMac()));
	}

	private function unban(User $u){
		exec(sprintf('sudo /usr/sbin/ipset -A ipmacs %s,%s', $u->getIp(), $u->getMac()));
	}
}