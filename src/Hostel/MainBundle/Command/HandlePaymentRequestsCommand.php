<?php
namespace Hostel\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\Payment;
use Hostel\MainBundle\Entity\PaymentRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HandlePaymentRequestsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('handlePaymentRequests')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$command = $this->getName();

		$search = shell_exec("pgrep -fl '$command' | grep -v 'console run'");
		$lines = explode("\n", trim($search));
		if(count($lines) > 2){
			$output->writeln('The process is already running');
			return;
		}

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var PaymentRequest[] $paymentRequests */
		$paymentRequests = $em->getRepository('HostelMainBundle:PaymentRequest')->findBy(array(
			'handled' => false
		), array('createdAt' => 'ASC'), 100);

		$costMonthly = $this->getContainer()->getParameter('cost_monthly');
		$costConnection = $this->getContainer()->getParameter('cost_connection');

		foreach($paymentRequests as $pr){
			$user = $pr->getUser();
			$month = $pr->getMonth();
			$year = $pr->getYear();
			$error = null;
			$cost = 0;

			if($pr->getConnection()){
				if($user->getConnectionPayed()){
					$error = 'Подключение уже оплачено';
				}

				foreach($user->getPaymentRequests() as $pr2){
					if($pr2 != $pr && !$pr2->getHandled() && $pr2->getConnection()){
						$error = 'Заявка на оплату еще в очереди, подождите';
						break;
					}
				}

				$cost = $costConnection;
			}else{
				foreach($user->getPayments() as $p){
					if($p->getMonth() == $month && $p->getYear() == $year){
						$error = 'Оплата на эту дату уже проставлена';
						break;
					}
				}

				foreach($user->getPaymentRequests() as $pr2){
					if($pr2 != $pr && !$pr2->getHandled() && $pr2->getMonth() == $month && $pr2->getYear() == $year){
						$error = 'Заявка на оплату еще в очереди, подождите';
						break;
					}
				}
				$cost = $costMonthly;
			}

			if($user->getMoney() < $cost){
				$error = 'Недостаточно средств';
			}

			if(!$error){
				try{
					if($pr->getConnection()){
						$user->setConnectionPayed(true);
					}else{
						$p = new Payment();
						$p
							->setMonth($month)
							->setYear($year)
							->setAmount($cost)
							->setUser($user)
						;
						$em->persist($p);
					}

					$user->setMoney($user->getMoney() - $cost);

					$em->persist($user);
					$em->flush();
				}catch(\Exception $e){
					$this->getContainer()->get('logger')->error(sprintf('Error while handling payment request: %s', $e->getMessage()));

					return;
				}
			}

			$pr
				->setHandled(true)
				->setError($error)
			;

			$em->persist($pr);
			$em->flush();
		}
	}
}