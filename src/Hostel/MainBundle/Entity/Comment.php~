<?php

namespace Hostel\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comment
 */
class Comment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \Hostel\MainBundle\Entity\User
     */
    private $user;

    /**
     * @var \Hostel\MainBundle\Entity\Ticket
     */
    private $ticket;


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
     * Set text
     *
     * @param string $text
     * @return Comment
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Comment
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
     * Set user
     *
     * @param \Hostel\MainBundle\Entity\User $user
     * @return Comment
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
     * Set ticket
     *
     * @param \Hostel\MainBundle\Entity\Ticket $ticket
     * @return Comment
     */
    public function setTicket(\Hostel\MainBundle\Entity\Ticket $ticket = null)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return \Hostel\MainBundle\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }
    /**
     * @var integer
     */
    private $newStatus;


    /**
     * Set newStatus
     *
     * @param integer $newStatus
     * @return Comment
     */
    public function setNewStatus($newStatus)
    {
        $this->newStatus = $newStatus;

        return $this;
    }

    /**
     * Get newStatus
     *
     * @return integer 
     */
    public function getNewStatus()
    {
        return $this->newStatus;
    }
    /**
     * @var integer
     */
    private $oldStatus;


    /**
     * Set oldStatus
     *
     * @param integer $oldStatus
     * @return Comment
     */
    public function setOldStatus($oldStatus)
    {
        $this->oldStatus = $oldStatus;

        return $this;
    }

    /**
     * Get oldStatus
     *
     * @return integer 
     */
    public function getOldStatus()
    {
        return $this->oldStatus;
    }
}
