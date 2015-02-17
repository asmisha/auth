<?php
/**
 * User: asmisha
 * Date: 28.04.14
 * Time: 11:00
 */

namespace Hostel\MainBundle\Admin;


use Hostel\MainBundle\Form\Type\HostelType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends \Sonata\UserBundle\Admin\Entity\UserAdmin{
	protected $datagridValues = array(
		'_page' => 1,
		'_sort_order' => 'DESC', // sort direction
		'_sort_by' => 'id' // field name
	);

	protected $translationDomain = 'SonataUserBundle';
	protected $labelTranslatorStrategy = 'sonata.admin.label.strategy.underscore';

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('id', null, array('route' => array('name' => 'show')))
			->add('username')
			->add('fullname')
			->add('hostel')
			->add('room')
			->add('connectionPayed', null, array('editable' => true))
			// You may also specify the actions you want to be displayed in the list
			->add('_action', 'actions', array(
				'actions' => array(
//					'show' => array(),
					'edit' => array(),
//					'delete' => array(),
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
			->add('id')
			->add('firstname')
			->add('middlename')
			->add('lastname')
			->add('hostel')
			->add('room')
			->add('enabled')
			->add('passportScans', null, array(
				'template' => 'HostelMainBundle:CRUD:user_passportScans_show.html.twig'
			))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('username')
			->add('plainPassword', 'password', array('required' => false))
			->add('firstname')
			->add('middlename')
			->add('lastname')
			->add('hostel', new HostelType())
			->add('room')
			->add('connectionPayed', null, array(
				'required' => false
			))
		;

		if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
			$formMapper
				->add('realRoles', 'sonata_security_roles', array(
					'label'    => 'form.label_roles',
					'expanded' => true,
					'multiple' => true,
					'required' => false,
//					'choices' => array(
//						'ROLE_ADMIN' => 'role.admin',
//						'ROLE_SUPER_ADMIN' => 'role.super_admin',
//					),
					'translation_domain' => $this->getTranslationDomain()
				))
				->add('roomPattern')
			;
		}

		$formMapper
			->add('payments', 'sonata_type_collection', array(
				'by_reference' => false,
				'attr'=>array('data-sonata-select2'=>'false'),
			), array(
				'edit' => 'inline',
				'inline' => 'table',
			))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $filterMapper)
	{
		$filterMapper
			->add('id')
			->add('username')
			->add('firstname')
			->add('lastname')
			->add('banned')
			->add('isAdmin')
		;
	}
} 