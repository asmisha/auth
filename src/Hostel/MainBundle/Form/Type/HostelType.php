<?php

namespace Hostel\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class HostelType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choices' => array(
				1 => 'hostel1',
				2 => 'hostel2',
				3 => 'hostel3',
				4 => 'hostel4',
			),
			'translation_domain' => 'hostels'
		));
	}

	public function getParent()
	{
		return 'choice';
	}

	public function getName()
	{
		return 'hostel';
	}
}