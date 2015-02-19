<?php

namespace Hostel\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class HostelType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$hostels = range(1, 4);
		$resolver->setDefaults(array(
			'choices' => array_combine($hostels, $hostels),
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