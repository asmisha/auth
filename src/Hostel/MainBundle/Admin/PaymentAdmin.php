<?php

namespace Hostel\MainBundle\Admin;


use Hostel\MainBundle\Entity\Payment;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PaymentAdmin extends Admin{
	protected $datagridValues = array(
		'_page' => 1,
		'_sort_order' => 'DESC', // sort direction
		'_sort_by' => 'id' // field name
	);

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('id', null, array('route' => array('name' => 'show')))
			->add('user')
			->add('month', 'choice', array(
				'template' => 'HostelMainBundle:CRUD:payment_list_month.html.twig'
			))
			->add('year')
			// You may also specify the actions you want to be displayed in the list
			->add('_action', 'actions', array(
				'actions' => array(
					'edit' => array(),
					'delete' => array(),
				)
			))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$isNested = $this->getParentFieldDescription() !== null;

		if(!$isNested){
			$formMapper
				->add('user')
			;
		}

		$months = range(1, 12);
		$years = array(
			date('Y'),
			date('Y') - 1,
			date('Y') + 1,
		);

		$formMapper
			->add('month', 'choice', array(
				'choices' => array_combine($months, $months),
				'translation_domain' => 'month',
			))
			->add('year', 'choice', array(
				'choices' => array_combine($years, $years)
			))
		;
	}
} 