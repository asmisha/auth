<?php

namespace Hostel\MainBundle\Services;

use Hostel\MainBundle\Entity\Operation;
use Hostel\MainBundle\Entity\User;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Symfony\Bridge\Monolog\Logger;
use Ijanki\Bundle\FtpBundle\Ftp;
use Doctrine\ORM\EntityManager;

class Erip {
	private $ftpHost, $ftpUser, $ftpPassword, $apiUrl;
	/** @var Ftp $ftp */
	private $ftp;
	/** @var Logger $logger */
	private $logger;
	/** @var EntityManager $em */
	private $em;

	public function __construct($ftpHost, $ftpUser, $ftpPassword, Logger $logger, Ftp $ftp, EntityManager $em){
		$this->ftpHost = $ftpHost;
		$this->ftpUser = $ftpUser;
		$this->ftpPassword = $ftpPassword;

		$this->logger = $logger;
		$this->ftp = $ftp;
		$this->em = $em;

		$this->connect();
	}

	public function __destruct(){
		try{
			$this->ftp->close();
		}catch(\Exception $e){}
	}

	private function connect(){
		try{
			$this->ftp->close();
		}catch(\Exception $e){}

		try {
			$this->ftp->connect($this->ftpHost);
			$this->ftp->login($this->ftpUser, $this->ftpPassword);
			$this->ftp->set_option(FTP_TIMEOUT_SEC, 10);
			$this->ftp->pasv(true);
		} catch (FtpException $e) {
			$this->logger->error('Can\'t connect or login: '.$e->getMessage());
			throw $e;
		}
	}

	public function uploadPaymentRequests(){
		$body = array();

		/** @var User[] $users */
		$users = $this->em->getRepository('HostelMainBundle:User')->findAll();

		foreach($users as $u){
			$info = array(
				'2', // тип сообщения
				$u->getId(), // номер лицевого счета
				sprintf('%s %s %s', $u->getFirstname(), $u->getMiddlename(), $u->getLastname()), // ФИО
				sprintf('%s %s %s, Общежитие №%d БГУИР, к. %s', $u->getFirstname(), $u->getMiddlename(), $u->getLastname(), $u->getHostel(), $u->getRoom()), // ФИО + адрес
				'', // период оплаты
				'0', // задолженность
				'', // счетчики
				date('YmdHis'), // дата формирования требования об оплате
				'',
				'',
				'',
				'',
				'',
				'',
				'',
			);

			$body[] = implode('^', $info);
		}

		$head = array(
			'5',
			'32102116', // код абонента - в параметры
			'1', // номер сообщения
			date('YmdHis'),
			count($body), // количество записей в сообщении
			'102287545', // учетный номер плательщика - в параметры
			'720', // код банка - в параметры
			'3632978040010', // расчетный счет - в параметры
			'1', // номер услуги (оплата за сеть) - в параметры
			'974', // код валюты - в параметры
			'PS', // параметры сообщения
		);

		array_unshift($body, implode('^', $head));

		$data = implode("\n", $body);
		$data = iconv('utf-8', 'cp1251', $data);

		$file = sprintf('in/%s.202', date('dmYHis'));
		$this->logger->info(sprintf('Uploading payment requests to file "%s"', $file));

		$this->ftp->putContents($file, $data, FTP_BINARY);
	}

	public function parsePayments(){
		$this->ftp->chdir('out');
		$files = $this->ftp->nlist('.');

		$pattern = '#^.*\.206$#';

		foreach($files as $file){
			if(preg_match($pattern, $file)){
				try{
					$this->parsePayment($file);
				}catch(\Exception $e){
					$this->logger->error($e->getMessage());
				}
			}
		}
	}

	private function parsePayment($file){
		$this->logger->info(sprintf('Parsing cards info file "%s"', $file));

		$data = trim(iconv('cp1251', 'utf-8', $this->getFileData($file)));

		$data = explode("\n", $data);
		$first = explode(';', array_shift($data));

		foreach($data as $item){
			$info = explode('^', $item);
			foreach($info as $k => $i)
				$info[$k] = trim($i);

			$userId = $info[2];
			$amount = $info[6];
			$date = $info[8];
			$operationId = $info[11];

			/** @var User $user */
			$user = $this->em->getRepository('HostelMainBundle:User')->find($userId);

			/** @var Operation $operation */
			$operation = $this->em->getRepository('HostelMainBundle:Operation')->findOneBy(array(
				'operationId' => $operationId,
			));

			if(!$operation){
				$operation = new Operation();

				if($user){
					$user->setMoney($user->getMoney() + $amount);
					$this->em->persist($user);
				}
			}

			$operation
				->setDate(\DateTime::createFromFormat('YmdHis', $date))
				->setAmount($amount)
				->setData($info)
				->setUser($user)
				->setOperationId($operationId)
			;

			$this->em->persist($operation);
		}

		$this->em->flush();

		$this->ftp->rename($file, 'bak/'.$file);

		return true;
	}

	function getFileData($filename) {
		$data = false;

		while(!$data){
			$this->logger->info(sprintf('Downloading file %s', $filename));

			$temp = fopen('php://temp', 'r+');
			if (@$this->ftp->fget($temp, $filename, FTP_BINARY, 0)) {
				rewind($temp);
				$data = stream_get_contents($temp);
			}else {
				$data = false;
			}

			fclose($temp);

			if(!$data){
				sleep(5);
				$this->connect();
			}
		}

		return $data;
	}
}