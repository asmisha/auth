<?php

namespace Hostel\MainBundle\Form\Type;

use Hostel\MainBundle\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketStatusType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$statuses = array(
			Ticket::STATUS_NEW,
			Ticket::STATUS_OPENED,
			Ticket::STATUS_CLOSED,
		);

		$resolver->setDefaults(array(
			'choices'=>array_combine($statuses, $statuses),
			'translation_domain' => 'TicketStatus',
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