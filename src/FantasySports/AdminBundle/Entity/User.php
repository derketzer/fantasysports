<?php

namespace FantasySports\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Wallet", mappedBy="user")
     */
    private $wallet;

    /**
     * @ORM\OneToMany(targetEntity="Pase", mappedBy="user")
     */
    private $passes;

    /**
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="user")
     */
    private $tickets;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registered_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $registeredAt;

    /**
     * @ORM\OneToMany(targetEntity="WalletTransaction", mappedBy="user")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="Ranking", mappedBy="user")
     */
    private $rankings;

    /**
     * @ORM\OneToMany(targetEntity="Coupon", mappedBy="user")
     */
    private $coupons;

    public function __construct()
    {
        parent::__construct();
        $this->passes = new ArrayCollection();
        $this->rankings = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->coupons = new ArrayCollection();
        $this->tickets = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCoupons()
    {
        return $this->coupons;
    }

    /**
     * @param mixed $coupons
     */
    public function setCoupons($coupons)
    {
        $this->coupons = $coupons;
    }

    /**
     * @return mixed
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param mixed $wallet
     */
    public function setWallet($wallet)
    {
        $this->wallet = $wallet;
    }

    /**
     * @return mixed
     */
    public function getPasses()
    {
        return $this->passes;
    }

    /**
     * @param mixed $passes
     */
    public function setPasses($passes)
    {
        $this->passes = $passes;
    }

    /**
     * @return \DateTime
     */
    public function getRegisteredAt()
    {
        return $this->registeredAt;
    }

    /**
     * @param \DateTime $registeredAt
     */
    public function setRegisteredAt($registeredAt)
    {
        $this->registeredAt = $registeredAt;
    }

    /**
     * @return mixed
     */
    public function getRankings()
    {
        return $this->rankings;
    }

    /**
     * @param mixed $rankings
     */
    public function setRankings($rankings)
    {
        $this->rankings = $rankings;
    }

    /**
     * @return mixed
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param mixed $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @return mixed
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param mixed $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }
}