<?php
/**
 * User: asmisha
 * Date: 28.04.14
 * Time: 11:00
 */

namespace Hostel\MainBundle\Admin;


use Doctrine\ORM\QueryBuilder;
use Hostel\MainBundle\Entity\Request;
use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\Type\RequestStatusType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class RequestAdmin extends Admin{
	protected $datagridValues = array(
		'_page' => 1,
		'_sort_order' => 'DESC', // sort direction
		'_sort_by' => 'date' // field name
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
			->add('user.room')
			->add('date')
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
			->add('status', new RequestStatusType())
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		/** @var User $admin */
		$admin = $this->getConfigurationPool()->getContainer()->get('security.context')->getToken()->getUser();

		$datagridMapper
			->add('status', 'doctrine_orm_choice', array(),
				new RequestStatusType(),
				array(
					'multiple' => true,
					'expanded' => true,
				)
			)
			->add('other', 'doctrine_orm_callback', array(
				'callback' => function($queryBuilder, $alias, $field, $value) use ($admin){
						/** @var QueryBuilder $queryBuilder */
						if (!$value['value']) {
							$queryBuilder
								->join(sprintf('%s.user', $alias), 'u')
								->andWhere('REGEXP(u.room, :regex) = 1 AND u.hostel = :hostel')
								->setParameter('regex', $admin->getRoomPattern())
								->setParameter('hostel', $admin->getHostel())
							;
							return true;
						}else{
							return false;
						}
					},
				'field_type' => 'checkbox'
			))
			->add('other_hostels', 'doctrine_orm_callback', array(
				'callback' => function($queryBuilder, $alias, $field, $value) use ($admin){
						/** @var QueryBuilder $queryBuilder */
						if (!$value['value']) {
							$queryBuilder
								->join(sprintf('%s.user', $alias), '__u')
								->andWhere('__u.hostel = :hostel')
								->setParameter('hostel', $admin->getHostel())
							;
							return true;
						}else{
							return false;
						}
					},
				'field_type' => 'checkbox',
			))
		;
	}
} 