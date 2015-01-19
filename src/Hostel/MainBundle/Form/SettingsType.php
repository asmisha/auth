<?php

namespace Hostel\MainBundle\Form;

use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\Type\AgreeWithTermsType;
use Hostel\MainBundle\Form\Type\FileListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class SettingsType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('oldPassword', 'password', array(
				'mapped' => false,
				'constraints' => new UserPassword(),
			))
			->add('username')
        	->add('plainPassword', 'repeated', array(
				'type' => 'password',
				'required' => false,
				'first_options'  => array('label' => 'Password'),
				'second_options' => array('label' => 'Retype Password'),
			))
			->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Hostel\MainBundle\Entity\User',
			'translation_domain' => 'User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hostel_mainbundle_user_settings';
    }
}
