<?php
/**
 * User: asmisha
 * Date: 28.04.14
 * Time: 11:00
 */

namespace Hostel\MainBundle\Admin;


use Hostel\MainBundle\Entity\Request;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class RequestAdmin extends Admin{
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
			->add('title')
			->add('description')
			->add('room')
			->add('admin')
			// You may also specify the actions you want to be displayed in the list
			->add('_action', 'actions', array(
				'actions' => array(
					'show' => array(),
					'edit' => array(),
					'delete' => array(),
					'view' => array(
						'template' => 'HostelMainBundle:CRUD:list__action_view.html.twig'
					),
				)
			))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
			->add('title')
			->add('description')
			->add('room')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('title')
			->add('description')
			->add('room')
			->add('status', 'choice', array(
				'choices'=>array(
					Request::STATUS_NEW => 'request.status.new',
					Request::STATUS_OPENED => 'request.status.opened',
					Request::STATUS_CLOSED => 'request.status.closed',
				),
				'translation_domain' => 'SonataAdminRequest'
			))
		;
	}
} 