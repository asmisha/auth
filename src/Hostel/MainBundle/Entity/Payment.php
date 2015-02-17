<?php

namespace Hostel\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 */
class Payment
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
     * @var integer
     */
    private $amount;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \Hostel\MainBundle\Entity\User
     */
    private $user;

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
     * @return Payment
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
     * @return Payment
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
     * Set amount
     *
     * @param integer $amount
     * @return Payment
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Payment
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
     * Set user
     *
     * @param \Hostel\MainBundle\Entity\User $user
     * @return Payment
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
     * @var \Hostel\MainBundle\Entity\User
     */
    private $createdBy;


    /**
     * Set createdBy
     *
     * @param \Hostel\MainBundle\Entity\User $createdBy
     * @return Payment
     */
    public function setCreatedBy(\Hostel\MainBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Hostel\MainBundle\Entity\User 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

	public function __toString(){
		return sprintf('%s %s', $this->month, $this->year);
	}
}
