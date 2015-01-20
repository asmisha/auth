<?php
namespace Hostel\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\Payment;
use Hostel\MainBundle\Entity\PaymentRequest;
use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Services\IpMac;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CorrectRulesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('cron:correctRules')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var IpMac $ipmac */
		$ipmac = $this->getContainer()->get('ipmac');
		$rules = $ipmac->listRules();

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var User[] $users */
		$users = $em->getRepository('HostelMainBundle:User')->findBy(array('banned' => false));

		foreach($users as $u){
			$k = sprintf('%s,%s', $u->getIp(), $u->getMac());
			if(isset($rules[$k])){
				$rules[$k] = true;
			}else{
				$ipmac->unban($u);
			}
		}

		foreach($rules as $k=>$i){
			if(!$i){
				list($ip, $mac) = explode(',', $k);
				$ipmac->banIpMac($ip, $mac);
			}
		}

		$em->flush();
	}
}