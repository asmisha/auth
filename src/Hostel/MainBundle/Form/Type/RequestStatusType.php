<?php

namespace Hostel\MainBundle\Form\Type;

use Hostel\MainBundle\Entity\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RequestStatusType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choices'=>array(
				Request::STATUS_NEW => 'request.status.new',
				Request::STATUS_OPENED => 'request.status.opened',
				Request::STATUS_CLOSED => 'request.status.closed',
			),
			'translation_domain' => 'SonataAdminRequest'
		));
	}

	public function getParent()
	{
		return 'choice';
	}

	public function getName()
	{
		return 'request_status';
	}
}