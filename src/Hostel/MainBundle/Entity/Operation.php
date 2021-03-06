<?php

namespace Hostel\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Operation
 */
class Operation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $operationId;

    /**
     * @var array
     */
    private $data;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return Operation
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Operation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set operationId
     *
     * @param integer $operationId
     * @return Operation
     */
    public function setOperationId($operationId)
    {
        $this->operationId = $operationId;

        return $this;
    }

    /**
     * Get operationId
     *
     * @return integer 
     */
    public function getOperationId()
    {
        return $this->operationId;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return Operation
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @var \Hostel\MainBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param \Hostel\MainBundle\Entity\User $user
     * @return Operation
     */
    public function setUser(\Hostel\MainBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Hostel\MainBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
