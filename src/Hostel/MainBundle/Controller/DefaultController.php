<?php

namespace Hostel\MainBundle\Controller;

use Hostel\MainBundle\Entity\Comment;
use Hostel\MainBundle\Entity\File;
use Hostel\MainBundle\Entity\PaymentRequest;
use Hostel\MainBundle\Entity\Request;
use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\CommentType;
use Hostel\MainBundle\Form\RequestType;
use Hostel\MainBundle\Form\SettingsType;
use Hostel\MainBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
    public function indexAction(\Symfony\Component\HttpFoundation\Request $httpRequest)
    {
		if($this->getUser()){
			$request = new Request();
			$request->setUser($this->getUser());

			$requestForm = $this->createForm(new RequestType(), $request);
			$settingsForm = $this->createForm(new SettingsType(), $this->getUser());

			$registerForm = null;
		}else{
			$requestForm = null;
			$settingsForm = null;

			$user = new User();
			$mac = $this->get('ipmac')->getMac($httpRequest->getClientIp());
			$user->setMac($mac);

			$registerForm = $this->createForm(new UserType(), $user);
		}

		if($httpRequest->getMethod() == 'POST'){
			/** @var Form[] $forms */
			$forms = array($requestForm, $registerForm, $settingsForm);

			foreach($forms as $form){
				if($form && $httpRequest->get($form->getName())){
					$form->submit($httpRequest->get($form->getName()));

					if($form->isValid()){
						if($form === $registerForm){
							$t = $httpRequest->files->get($form->getName());
							foreach($t['passportScans'] as $f){
								$file = new File();
								$file->setFile($f);
								$file->upload();

								$form->getData()->addPassportScan($file);
							}
						}

						$em = $this->getDoctrine()->getManager();
						$em->persist($form->getData());
						$em->flush();

						if($form === $requestForm){
							return $this->redirect($this->generateUrl('request', array('id' => $form->getData()->getId())));
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
			'requestForm' => $requestForm ? $requestForm->createView() : null,
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

	public function requestAction(\Symfony\Component\HttpFoundation\Request $httpRequest, $id){
		/** @var Request $request */
		$request = $this->getDoctrine()->getRepository('HostelMainBundle:Request')->find($id);

		if(!$request)
			return $this->createNotFoundException('');

		if(!$this->getUser()->getIsAdmin() && $this->getUser() != $request->getUser()){
			return $this->createAccessDeniedException();
		}

		$comment = new Comment();
		$commentForm = $this->createForm(new CommentType(), $comment);

		if($httpRequest->getMethod() == 'POST'){
			$commentForm->submit($httpRequest);

			if($commentForm->isValid()){
				$em = $this->getDoctrine()->getManager();

				$em->persist($comment);
				$em->flush();

				return $this->redirect($this->generateUrl('request', array('id' => $id)));
			}
		}

		return $this->render('HostelMainBundle:Default:request.html.twig', array(
			'request' => $request,
			'commentForm' => $commentForm->createView()
		));
	}

	public function requestAddCommentAction(\Symfony\Component\HttpFoundation\Request $httpRequest, $id){
		$request = $this->getDoctrine()->getRepository('HostelMainBundle:Request')->find($id);

		if(!$request)
			return $this->createNotFoundException('');

		if(!$this->getUser()->getIsAdmin() && $this->getUser() != $request->getUser()){
			return $this->createAccessDeniedException();
		}

		$comment = new Comment();
		$comment->setRequest($request);
		$commentForm = $this->createForm(new CommentType(), $comment);

		if($httpRequest->getMethod() == 'POST'){
			$commentForm->submit($httpRequest);

			if($commentForm->isValid()){
				$em = $this->getDoctrine()->getManager();

				$em->persist($comment);
				$em->flush();

				return $this->redirect($this->generateUrl('request', array('id' => $id)));
			}
		}else
			throw new \Exception('Empty data');

		return null;
	}
}
