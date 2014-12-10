<?php
namespace Hostel\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\User;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ijanki\Bundle\FtpBundle\Ftp;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('upload')
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

		/** @var User[] $users */
		$users = $em->getRepository('HostelMainBundle:User')->createQueryBuilder('u')
			->select('')
			->getQuery()
			->getResult()
		;

		$lines = array();

		foreach($users as $u){
			$addresses = $this->getContainer()->getParameter('hostel_addresses');
			$address = $u->getHostel() ? $addresses[$u->getHostel()] : null;

			$lines[] = implode('^', array(
				'2',
				$u->getId(),
				sprintf('%s %s %s', $u->getLastname(), $u->getFirstname(), $u->getMiddlename()),
				$address,
				'', // период
				0,  // задолженность
				'', // счетчики
				'', // дата формирования требования
				'', // доп. информация
				'', // доп. данные
				'', // пеня
				'', // проживающие
				'', // льготники
				'', // коэфициент 1
				'', // коэфициент 2
			));
		}

		array_unshift($lines, implode('^', array(
			'5',
			$this->getContainer()->getParameter('raschet_code'),
			'1', // номер сообщения. всего или в день?
			date('YmdHis'),
			count($lines),
			$this->getContainer()->getParameter('raschet_account_id'),
			$this->getContainer()->getParameter('raschet_bank_code'),
			$this->getContainer()->getParameter('raschet_account_number'),
			$this->getContainer()->getParameter('raschet_service_number'),
			$this->getContainer()->getParameter('raschet_currency_code'),
			'PS'
		)));

		$data = implode("\n", $lines);

		$this->upload($data);
	}

	public function upload($data){
		$ftpHost = $this->getContainer()->getParameter('raschet_ftp_host');
		$ftpUser = $this->getContainer()->getParameter('raschet_ftp_user');
		$ftpPassword = $this->getContainer()->getParameter('raschet_ftp_password');
		$ftpDir = $this->getContainer()->getParameter('raschet_ftp_upload_dir');

		/** @var Logger $logger */
		$logger = $this->getContainer()->get('logger');
		/** @var Ftp $ftp */
		$ftp = $this->getContainer()->get('ijanki_ftp');

		try {
			$ftp->connect($ftpHost);
			$ftp->login($ftpUser, $ftpPassword);
		} catch (FtpException $e) {
			$logger->error('Can\'t connect or login: '.$e->getMessage());
			throw $e;
		}

		try {
			@$ftp->mkDirRecursive($ftpDir);
		} catch (FtpException $e) {}

		$fileName = sprintf('MC-%s.202', date('Ymd'));
		$filePath = $ftpDir.'/'.$fileName;

		try {
			$logger->debug(sprintf('Writing file %s', $filePath));
			$ftp->putContents($filePath, $data, FTP_BINARY);
		} catch (FtpException $e) {
			$logger->error(sprintf('Can\'t write file %s', $filePath));
			return false;
		}

		return true;
	}
}