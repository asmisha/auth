<?php

namespace Hostel\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class FileListType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'mapped' => true,
			'multiple' => true,
		));
	}

	public function getParent()
	{
		return 'file';
	}

	public function getName()
	{
		return 'filelist';
	}
}