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

		$hostels = array_keys($this->getContainer()->getParameter('hostel_addresses'));
		$rules = array();

		foreach($hostels as $h){
			$rules = array_merge($rules, $ipmac->listRules($h));
		}

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var User[] $users */
		$users = $em->getRepository('HostelMainBundle:User')->createQueryBuilder('u')
			->where('u.ip IS NOT NULL AND u.mac IS NULL')
			->getQuery()
			->getResult()
		;

		foreach($users as $u){
			if($mac = $ipmac->getMac($u)){
				$u->setMac($mac);
				$output->writeln(sprintf('Updated mac for ip %s: %s', $u->getIp(), $u->getMac()));
			}else{
				$u
					->setIp(null)
					->setBanned(true)
				;
			}
		}
		$em->flush();

		/** @var User[] $users */
		$users = $em->getRepository('HostelMainBundle:User')->findBy(array(
			'banned' => false,
		));

		foreach ($users as $u) {
			$k = strtoupper(sprintf('%s,%s', $u->getIp(), $u->getMac()));
			if (isset($rules[$k])) {
				$rules[$k] = true;
			} else {
				$output->writeln(sprintf('Unbanning user %d %s %s %s %s', $u->getId(), $u->getFirstname(), $u->getLastname(), $u->getIp(), $u->getMac()));

				$ipmac->unban($u);
			}
		}

		foreach ($rules as $k => $i) {
			if (!$i) {
				$data = explode(',', $k);
				if(count($data) != 2){
					continue;
				}
				list($ip, $mac) = $data;

				$output->writeln(sprintf('Banning %s %s', $ip, $mac));

				$ipmac->banIpMac($ip, $mac);
			}
		}

		$em->flush();
	}
}