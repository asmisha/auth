<?php
/**
 * User: asmisha
 * Date: 02.12.14
 * Time: 16:55
 */

namespace Hostel\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EripParsePaymentsCommand extends ContainerAwareCommand {
	public function configure(){
		$this
			->setName('cron:erip:parsePayments')
		;
	}

	public function run(InputInterface $input, OutputInterface $output){
		$this->getContainer()->get('erip')->parsePayments();
	}
} 