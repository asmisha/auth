<?php

namespace Hostel\MainBundle\Services\Twig;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityExtension extends \Twig_Extension
{
	/** @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter $csrfProvider */
	private $csrfProvider;
	/** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
	private $requestStack;

	public function __construct(CsrfTokenManagerAdapter $csrfProvider, RequestStack $requestStack){
		$this->csrfProvider = $csrfProvider;
		$this->requestStack = $requestStack;
	}

	public function getFunctions(){
		return array(
			new \Twig_SimpleFunction('getAuthCsrfToken', array($this, 'getAuthCsrfToken')),
			new \Twig_SimpleFunction('getLastLogin', array($this, 'getLastLogin')),
			new \Twig_SimpleFunction('getLoginError', array($this, 'getLoginError')),
		);
	}

	public function getAuthCsrfToken(){
		return $this->csrfProvider->generateCsrfToken('authenticate');
	}

	public function getLastLogin(){
		$request = $this->requestStack->getCurrentRequest();
		/* @var $session \Symfony\Component\HttpFoundation\Session\Session */
		$session = $request->getSession();

		// last username entered by the user
		$lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

		return $lastUsername;
	}

	public function getLoginError(){
		$request = $this->requestStack->getCurrentRequest();
		$session = $request->getSession();

		// get the error if any (works with forward and redirect -- see below)
		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		} elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
			$session->remove(SecurityContext::AUTHENTICATION_ERROR);
		} else {
			$error = '';
		}

		if ($error) {
			// TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
			$error = $error->getMessage();
		}

		return $error;
	}

	public function getName()
	{
		return 'security_extension';
	}
}