<?php

namespace Hostel\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentRequest
 */
class PaymentRequest
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $year;

    /**
     * @var integer
     */
    private $month;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var boolean
     */
    private $handled = false;

	public function __construct(){
		$this->createdAt = new \DateTime();
	}


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
     * Set year
     *
     * @param integer $year
     * @return PaymentRequest
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param integer $month
     * @return PaymentRequest
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return integer 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PaymentRequest
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set handled
     *
     * @param boolean $handled
     * @return PaymentRequest
     */
    public function setHandled($handled)
    {
        $this->handled = $handled;

        return $this;
    }

    /**
     * Get handled
     *
     * @return boolean 
     */
    public function getHandled()
    {
        return $this->handled;
    }
    /**
     * @var \Hostel\MainBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param \Hostel\MainBundle\Entity\User $user
     * @return PaymentRequest
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
    /**
     * @var string
     */
    private $error;


    /**
     * Set error
     *
     * @param string $error
     * @return PaymentRequest
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string 
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * @var boolean
     */
    private $connection = false;


    /**
     * Set connection
     *
     * @param boolean $connection
     * @return PaymentRequest
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get connection
     *
     * @return boolean 
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
