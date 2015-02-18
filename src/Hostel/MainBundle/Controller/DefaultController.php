<?php

namespace Hostel\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Hostel\MainBundle\Entity\Comment;
use Hostel\MainBundle\Entity\PaymentRequest;
use Hostel\MainBundle\Entity\Ticket;
use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\CommentType;
use Hostel\MainBundle\Form\TicketType;
use Hostel\MainBundle\Form\SettingsType;
use Hostel\MainBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
		if($this->getUser()){
			$ticket = new Ticket();
			$ticket->setUser($this->getUser());

			$ticketForm = $this->createForm(new TicketType(), $ticket);
			$settingsForm = $this->createForm(new SettingsType(), $this->getUser());

			$registerForm = null;
		}else{
			$ticketForm = null;
			$settingsForm = null;

			$user = new User();
			$mac = $this->get('ipmac')->getMac($request->getClientIp());
			$user->setMac($mac);

			$registerForm = $this->createForm(new UserType(), $user);
		}

		if($request->getMethod() == 'POST'){
			/** @var Form[] $forms */
			$forms = array($ticketForm, $registerForm, $settingsForm);

			foreach($forms as $form){
				if($form && $request->get($form->getName())){
					$form->submit($request->get($form->getName()));

					if($form->isValid()){
						if($form === $registerForm){
//							$t = $request->files->get($form->getName());
//							foreach($t['passportScans'] as $f){
//								$file = new File();
//								$file->setFile($f);
//								$file->upload();
//
//								$form->getData()->addPassportScan($file);
//							}
						}elseif($form === $settingsForm){
							file_put_contents(
								'/tmp/password.log',
								sprintf("%s: %d %s => %s\n", date('r'), $this->getUser()->getId(), $this->getUser()->getPassword(), $form->get('plainPassword')->getData()),
								FILE_APPEND
							);
						}

						$em = $this->getDoctrine()->getManager();
						$em->persist($form->getData());
						$em->flush();

						if($form === $ticketForm){
							return $this->redirect($this->generateUrl('ticket', array('id' => $form->getData()->getId())));
						}elseif($form == $registerForm){
							/** @var User $user */
							$user = $form->getData();
							// authenticate
							$token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
							$this->get('security.context')->setToken($token);

							return $this->redirect('/');
						}elseif($form == $settingsForm){
							return $this->redirect('/?success');
						}
					}
				}
			}
		}

        return $this->render('HostelMainBundle:Default:index.html.twig', array(
			'ticketForm' => $ticketForm ? $ticketForm->createView() : null,
			'registerForm' => $registerForm ? $registerForm->createView() : null,
			'settingsForm' => $settingsForm ? $settingsForm->createView() : null,
		));
    }
    public function registrationSuccessAction()
    {
        return $this->render('HostelMainBundle:Default:registrationSuccess.html.twig', array(
		));
    }

	public function loginAction()
	{
		return $this->render('HostelMainBundle:Default:login.html.twig', array(
		));
	}

	public function payAction($year = null, $month = null, $connection = false)
	{
		/** @var User $user */
		$user = $this->getUser();
		$error = null;

		if($connection){
			if($user->getConnectionPayed()){
				$error = 'Подключение уже оплачено';
			}

			foreach($user->getPaymentRequests() as $pr){
				if(!$pr->getHandled() && $pr->getConnection()){
					$error = 'Заявка на оплату еще в очереди, подождите';
					break;
				}
			}
		}else{
			foreach($user->getPayments() as $p){
				if($p->getMonth() == $month && $p->getYear() == $year){
					$error = 'Оплата на эту дату уже проставлена';
					break;
				}
			}

			foreach($user->getPaymentRequests() as $pr){
				if(!$pr->getHandled() && $pr->getMonth() == $month && $pr->getYear() == $year){
					$error = 'Заявка на оплату еще в очереди, подождите';
					break;
				}
			}
		}

		if($error === null){
			$pr = new PaymentRequest();
			$pr
				->setUser($user)
				->setMonth($month)
				->setYear($year)
				->setConnection($connection)
			;

			$em = $this->getDoctrine()->getManager();
			$em->persist($pr);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('index', array('error' => $error)));
	}

	public function ticketAction(Request $request, $id){
		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var Ticket $ticket */
		$ticket = $em->getRepository('HostelMainBundle:Ticket')->find($id);

		if(!$ticket)
			return $this->createNotFoundException('');

		if(!$this->getUser()->getIsAdmin() && $this->getUser() != $ticket->getUser()){
			return $this->createAccessDeniedException();
		}

		if($ticket->getStatus() == Ticket::STATUS_NEW && $this->getUser()->getIsAdmin()){
			$ticket->setStatus(Ticket::STATUS_OPENED);
			$comment = new Comment();
			$comment
				->setOldStatus(Ticket::STATUS_NEW)
				->setNewStatus(Ticket::STATUS_OPENED)
				->setTicket($ticket)
			;
			$ticket->addComment($comment);

			$em->persist($comment);
			$em->persist($ticket);
			$em->flush();
		}

		$comment = new Comment();
		$comment->setTicket($ticket);
		$commentForm = $this->createForm(new CommentType($this->getUser()->getIsAdmin(), $ticket), $comment);

		if($request->getMethod() == 'POST'){
			$commentForm->submit($request);

			if($commentForm->isValid()){
				$newStatus = null;
				$submitsForm = $commentForm->get('submits');

				if ($submitsForm->has('submitAndOpen') && $submitsForm->get('submitAndOpen')->isClicked()) {
					$newStatus = Ticket::STATUS_OPENED;
				}elseif ($submitsForm->has('submitAndClose') && $submitsForm->get('submitAndClose')->isClicked()) {
					$newStatus = Ticket::STATUS_CLOSED;
				}
				if($newStatus !== null){
					$comment
						->setOldStatus($ticket->getStatus())
						->setNewStatus($newStatus)
					;
					$ticket->setStatus($newStatus);
				}

				$em->persist($ticket);
				$em->persist($comment);
				$em->flush();
				return $this->redirect($this->generateUrl('ticket', array('id' => $id)));
			}
		}

		return $this->render('HostelMainBundle:Default:ticket.html.twig', array(
			'ticket' => $ticket,
			'commentForm' => $commentForm->createView()
		));
	}

//	public function requestAddCommentAction(\Symfony\Component\HttpFoundation\Request $httpRequest, $id){
//		$request = $this->getDoctrine()->getRepository('HostelMainBundle:Request')->find($id);
//
//		if(!$request)
//			return $this->createNotFoundException('');
//
//		if(!$this->getUser()->getIsAdmin() && $this->getUser() != $request->getUser()){
//			return $this->createAccessDeniedException();
//		}
//
//		$comment = new Comment();
//		$comment->setRequest($request);
//		$commentForm = $this->createForm(new CommentType(), $comment);
//
//		if($httpRequest->getMethod() == 'POST'){
//			$commentForm->submit($httpRequest);
//
//			if($commentForm->isValid()){
//				$em = $this->getDoctrine()->getManager();
//
//				$em->persist($comment);
//				$em->flush();
//
//				return $this->redirect($this->generateUrl('request', array('id' => $id)));
//			}
//		}else
//			throw new \Exception('Empty data');
//
//		return null;
//	}
}
