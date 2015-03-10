<?php
/**
 * User: asmisha
 * Date: 28.04.14
 * Time: 11:00
 */

namespace Hostel\MainBundle\Admin;


use Doctrine\ORM\QueryBuilder;
use Hostel\MainBundle\Entity\User;
use Hostel\MainBundle\Form\Type\TicketStatusType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class TicketAdmin extends Admin{
	protected $datagridValues = array(
		'status' => array('value' => array(1, 2)),
		'my_tickets' => array('value' => true),
		'my_hostel' => array('value' => true),
		'_page' => 1,
		'_sort_order' => 'DESC', // sort direction
		'_sort_by' => 'date' // field name
	);

	protected function configureRoutes(RouteCollection $collection){
		$collection->remove('edit');
		$collection->remove('delete');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('id', null, array('route' => array('name' => 'show')))
			->add('title')
			->add('description')
			->add('user')
			->add('user.room')
			->add('date')
			->add('status', 'text', array(
				'template' => 'HostelMainBundle:CRUD:ticket_list_status.html.twig'
			))
			// You may also specify the actions you want to be displayed in the list
			->add('_action', 'actions', array(
				'actions' => array(
					'show' => array(
						'template' => 'HostelMainBundle:CRUD:ticket_list__action_show.html.twig'
					),
					'edit' => array(),
					'delete' => array(),
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
			->add('status', new TicketStatusType())
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
				new TicketStatusType(),
				array(
					'multiple' => true,
					'expanded' => true,
					'data' => array(1, 2),
				)
			)
			->add('my_tickets', 'doctrine_orm_callback', array(
				'callback' => function($queryBuilder, $alias, $field, $value) use ($admin){
						/** @var QueryBuilder $queryBuilder */
						if ($value['value']) {
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
			->add('my_hostel', 'doctrine_orm_callback', array(
				'callback' => function($queryBuilder, $alias, $field, $value) use ($admin){
						/** @var QueryBuilder $queryBuilder */
						if ($value['value']) {
							$queryBuilder
								->join(sprintf('%s.user', $alias), '_u')
								->andWhere('_u.hostel = :hostel')
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