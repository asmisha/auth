<?php

namespace Hostel\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class AgreeWithTermsType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'mapped' => false,
			'required' => true,
			'constraints' => new True(array('message' => 'You must agree with terms and conditions to continue'))
		));
	}

	public function getParent()
	{
		return 'checkbox';
	}

	public function getName()
	{
		return 'agree_with_terms';
	}
}