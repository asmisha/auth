<?php

namespace Hostel\MainBundle\Form;

use Hostel\MainBundle\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
	private $isAdmin;
	/** @var Ticket */
	private $ticket;

	/**
	 * @param bool $isAdmin
	 * @param Ticket $ticket
	 */
	function __construct($isAdmin = false, $ticket = null)
	{
		$this->isAdmin = $isAdmin;
		$this->ticket = $ticket;
	}


	/**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$submits = $builder->create('submits', 'form', array(
			'virtual' => true,
			'label' => false,
			'attr' => array('class' => 'form-inline')
		))
			->add('submit', 'submit', array(
				'attr' => array(
					'class' => 'btn-primary',
				)
			))
		;

		if($this->isAdmin && $this->ticket){
			$open = $this->ticket->getStatus() == Ticket::STATUS_CLOSED;
			$submits
				->add($open ? 'submitAndOpen' : 'submitAndClose', 'submit', array(
					'attr' => array(
						'class' => $open ? 'btn-success' : 'btn-danger'
					)
				))
			;
		}

        $builder
            ->add('text')
            ->add($submits)
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Hostel\MainBundle\Entity\Comment',
			'translation_domain' => 'Comment',
			'label_format' => '%name%'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hostel_mainbundle_comment';
    }
}
