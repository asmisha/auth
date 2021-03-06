<?php

namespace Hostel\MainBundle\Form;

use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\Type\AgreeWithTermsType;
use Hostel\MainBundle\Form\Type\FileListType;
use Hostel\MainBundle\Form\Type\HostelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
	private $hideFields = array();

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
			->add('lastname')
			->add('firstname')
			->add('middlename')
			->add('groupNumber')
			->add('hostel', new HostelType())
			->add('room')
//			->add('passportScans', new FileListType(), array(
//				'mapped' => false
//			))
			->add('agreeWithTerms', new AgreeWithTermsType())
			->add('submit', 'submit')
        ;

		$hideFields = $this->hideFields;
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($hideFields){
			$user = $event->getData();
			$form = $event->getForm();

			if(!in_array('plainPassword', $hideFields)){
				$options = array(
					'first_options'  => array('label' => ($user && $user->getId() ? 'New Password' : 'Password')),
					'second_options' => array('label' => ($user && $user->getId() ? 'Retype New Password' : 'Retype Password')),
					'type' => 'password',
					'required' => (!$user || !$user->getId()),
				);
				if(!$user || !$user->getId())
					$options['constraints'] = array(new NotBlank());

				$form
					->add('plainPassword', 'repeated', $options)
				;
			}

			if(!in_array('password', $hideFields) && $user && $user->getId()){
				$form->add('password', 'password', array(
					'required' => false,
					'mapped' => false,
					'label' => 'Current password'
				));
			}
		});

		$builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
			/** @var User $user */
			$user = $event->getData();

			if($user && $user->getPlainPassword()){
				// important to fire UserSubsriber events
				$user->setUpdatedAt(new \DateTime());
			}
		});

		$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
			$data = $event->getData();
			$form = $event->getForm();

			if($form->has('password') && ($data['plainPassword']['first'] || $data['plainPassword']['second'])){
				$form->add('password', 'password', array(
					'required' => false,
					'mapped' => false,
					'constraints' => new UserPassword()
				));
			}
		});
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
        return 'hostel_mainbundle_user';
    }
}
