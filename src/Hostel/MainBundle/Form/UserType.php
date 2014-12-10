<?php

namespace Hostel\MainBundle\Form;

use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\DataTransformer\FileListToUploadedFileTransform;
use Hostel\MainBundle\Form\Type\AgreeWithTermsType;
use Hostel\MainBundle\Form\Type\FileListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
			->add('hostel', 'choice', array(
				'choices' => array(
					1 => 'hostel1',
					2 => 'hostel2',
					3 => 'hostel3',
					4 => 'hostel4',
				),
				'translation_domain' => 'hostels'
			))
			->add('room')
			->add('passportScans', new FileListType(), array(
				'mapped' => false
			))

//			->add('passportScans', 'sonata_type_collection', array(
//				// Prevents the "Delete" option from being displayed
//				'type_options' => array('delete' => false)
//			), array(
//				'edit' => 'inline',
//				'inline' => 'table',
//				'sortable' => 'position',
//			))
			->add('agreeWithTerms', new AgreeWithTermsType())
			->add('submit', 'submit')
        ;

		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
			$user = $event->getData();
			$form = $event->getForm();

			if(!in_array('plainPassword', $this->hideFields)){
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

			if(!in_array('password', $this->hideFields) && $user && $user->getId()){
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
