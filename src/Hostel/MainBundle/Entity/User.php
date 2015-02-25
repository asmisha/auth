<?php

namespace Hostel\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;
use Sonata\UserBundle\Entity\BaseUser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var string
     */
    private $roomPattern;

    /**
     * Set roomPattern
     *
     * @param string $roomPattern
     * @return User
     */
    public function setRoomPattern($roomPattern)
    {
        $this->roomPattern = $roomPattern;

        return $this;
    }

    /**
     * Get roomPattern
     *
     * @return string 
     */
    public function getRoomPattern()
    {
        return $this->roomPattern;
    }
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
		parent::__construct();

        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->passportScans = new \Doctrine\Common\Collections\ArrayCollection();
		$this->emailCanonical = $this->email = md5(mt_rand().time()).'@tut.by';
		$this->enabled = true;
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
     * Add groups
     *
     * @param \Application\Sonata\UserBundle\Entity\Group $groups
     * @return User
     */
    public function addGroup(GroupInterface $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Application\Sonata\UserBundle\Entity\Group $groups
     */
    public function removeGroup(GroupInterface $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

	public function __toString(){
		if($this->firstname)
			return $this->firstname.' '.$this->lastname;
		else
			return $this->username;
	}
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tickets;


    /**
     * Add tickets
     *
     * @param \Hostel\MainBundle\Entity\Ticket $tickets
     * @return User
     */
    public function addTicket(\Hostel\MainBundle\Entity\Ticket $tickets)
    {
        $this->tickets[] = $tickets;

        return $this;
    }

    /**
     * Remove tickets
     *
     * @param \Hostel\MainBundle\Entity\Ticket $tickets
     */
    public function removeTicket(\Hostel\MainBundle\Entity\Ticket $tickets)
    {
        $this->tickets->removeElement($tickets);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTickets()
    {
        return $this->tickets;
    }
    /**
     * @var string
     */
    private $middlename;

    /**
     * @var string
     */
    private $hostel;

    /**
     * @var string
     */
    private $room;


    /**
     * Set middlename
     *
     * @param string $middlename
     * @return User
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middlename
     *
     * @return string 
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set hostel
     *
     * @param string $hostel
     * @return User
     */
    public function setHostel($hostel)
    {
        $this->hostel = $hostel;

        return $this;
    }

    /**
     * Get hostel
     *
     * @return string 
     */
    public function getHostel()
    {
        return $this->hostel;
    }

    /**
     * Set room
     *
     * @param string $room
     * @return User
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return string 
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $payments;

    /**
     * Add payments
     *
     * @param \Hostel\MainBundle\Entity\Payment $payments
     * @return User
     */
    public function addPayment(\Hostel\MainBundle\Entity\Payment $payments)
    {
        $this->payments[] = $payments;
		$payments->setUser($this);

        return $this;
    }

    /**
     * Remove payments
     *
     * @param \Hostel\MainBundle\Entity\Payment $payments
     */
    public function removePayment(\Hostel\MainBundle\Entity\Payment $payments)
    {
        $this->payments->removeElement($payments);
		$payments->setUser(null);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection|Payment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }
    /**
     * @var integer
     */
    private $money = 0;


    /**
     * Set money
     *
     * @param integer $money
     * @return User
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get money
     *
     * @return integer 
     */
    public function getMoney()
    {
        return $this->money;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $paymentRequests;


    /**
     * Add paymentRequests
     *
     * @param \Hostel\MainBundle\Entity\PaymentRequest $paymentRequests
     * @return User
     */
    public function addPaymentRequest(\Hostel\MainBundle\Entity\PaymentRequest $paymentRequests)
    {
        $this->paymentRequests[] = $paymentRequests;

        return $this;
    }

    /**
     * Remove paymentRequests
     *
     * @param \Hostel\MainBundle\Entity\PaymentRequest $paymentRequests
     */
    public function removePaymentRequest(\Hostel\MainBundle\Entity\PaymentRequest $paymentRequests)
    {
        $this->paymentRequests->removeElement($paymentRequests);
    }

    /**
     * Get paymentRequests
     *
     * @return \Doctrine\Common\Collections\Collection|PaymentRequest[]
     */
    public function getPaymentRequests()
    {
        return $this->paymentRequests;
    }

	public function getPaymentInfo(){
		$result = array();

		foreach($this->getPayments() as $p){
			$result[$p->getYear()][$p->getMonth()]['payed'] = $p;
		}
		foreach($this->getPaymentRequests() as $pr){
			if($pr->getConnection()){
				$result['connection']['paymentRequests'][] = $pr;
			}else{
				$result[$pr->getYear()][$pr->getMonth()]['paymentRequests'][] = $pr;
			}
		}

		return $result;
	}
    /**
     * @var string
     */
    private $groupNumber;


    /**
     * Set groupNumber
     *
     * @param string $groupNumber
     * @return User
     */
    public function setGroupNumber($groupNumber)
    {
        $this->groupNumber = $groupNumber;

        return $this;
    }

    /**
     * Get groupNumber
     *
     * @return string 
     */
    public function getGroupNumber()
    {
        return $this->groupNumber;
    }
    /**
     * @var boolean
     */
    private $connectionPayed = false;


    /**
     * Set connectionPayed
     *
     * @param boolean $connectionPayed
     * @return User
     */
    public function setConnectionPayed($connectionPayed)
    {
        $this->connectionPayed = $connectionPayed;

        return $this;
    }

    /**
     * Get connectionPayed
     *
     * @return boolean 
     */
    public function getConnectionPayed()
    {
        return $this->connectionPayed;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $passportScans;

    /**
     * Add passportScans
     *
     * @param \Hostel\MainBundle\Entity\File $passportScans
     * @return User
     */
    public function addPassportScan(\Hostel\MainBundle\Entity\File $passportScans)
    {
        $this->passportScans[] = $passportScans;

        return $this;
    }

    /**
     * Remove passportScans
     *
     * @param \Hostel\MainBundle\Entity\File $passportScans
     */
    public function removePassportScan(\Hostel\MainBundle\Entity\File $passportScans)
    {
        $this->passportScans->removeElement($passportScans);
    }

    /**
     * Get passportScans
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPassportScans()
    {
        return $this->passportScans;
    }
    /**
     * @var boolean
     */
    private $isAdmin = false;


    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return User
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean 
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }
    /**
     * @var string
     */
    private $mac;


    /**
     * Set mac
     *
     * @param string $mac
     * @return User
     */
    public function setMac($mac)
    {
        $this->mac = $mac;

        return $this;
    }

    /**
     * Get mac
     *
     * @return string 
     */
    public function getMac()
    {
        return $this->mac;
    }
    /**
     * @var string
     */
    private $ip;


    /**
     * Set ip
     *
     * @param string $ip
     * @return User
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }
    /**
     * @var boolean
     */
    private $banned = true;


    /**
     * Set banned
     *
     * @param boolean $banned
     * @return User
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    /**
     * Get banned
     *
     * @return boolean 
     */
    public function getBanned()
    {
        return $this->banned;
    }
    /**
     * @var boolean
     */
    private $checked = false;


    /**
     * Set checked
     *
     * @param boolean $checked
     * @return User
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get checked
     *
     * @return boolean 
     */
    public function getChecked()
    {
        return $this->checked;
    }

	public function setPlainPassword($plainPassword){
		if($plainPassword){
			parent::setPlainPassword($plainPassword);
			$this->setUpdatedAt(new \DateTime());
		}
	}

	public function getFullName(){
		return sprintf('%s %s %s', $this->lastname, $this->firstname, $this->middlename);
	}
    /**
     * @var string
     */
    private $vkLink;


    /**
     * Set vkLink
     *
     * @param string $vkLink
     * @return User
     */
    public function setVkLink($vkLink)
    {
        $this->vkLink = $vkLink;

        return $this;
    }

    /**
     * Get vkLink
     *
     * @return string 
     */
    public function getVkLink()
    {
        return $this->vkLink;
    }
    /**
     * @var string
     */
    private $adminDescription;


    /**
     * Set adminDescription
     *
     * @param string $adminDescription
     * @return User
     */
    public function setAdminDescription($adminDescription)
    {
        $this->adminDescription = $adminDescription;

        return $this;
    }

    /**
     * Get adminDescription
     *
     * @return string 
     */
    public function getAdminDescription()
    {
        return $this->adminDescription;
    }
    /**
     * @var string
     */
    private $position;


    /**
     * Set position
     *
     * @param string $position
     * @return User
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }
    /**
     * @var string
     */
    private $skype;

    /**
     * @var string
     */
    private $phoneNumber;


    /**
     * Set skype
     *
     * @param string $skype
     * @return User
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * Get skype
     *
     * @return string 
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string 
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }
    /**
     * @var string
     */
    private $actualEmail;


    /**
     * Set actualEmail
     *
     * @param string $actualEmail
     * @return User
     */
    public function setActualEmail($actualEmail)
    {
        $this->actualEmail = $actualEmail;

        return $this;
    }

    /**
     * Get actualEmail
     *
     * @return string 
     */
    public function getActualEmail()
    {
        return $this->actualEmail;
    }
}
