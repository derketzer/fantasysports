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
     * @ORM\OneToMany(targetEntity="Invitation", mappedBy="from")
     */
    protected $invitations;

    /**
     * @ORM\OneToOne(targetEntity="Invitation")
     * @ORM\JoinColumn(referencedColumnName="code")
     * @Assert\NotNull(message="Your invitation is wrong", groups={"Registration"})
     */
    protected $invitation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registered_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $registeredAt;

    public function __construct()
    {
        parent::__construct();
        $this->passes = new ArrayCollection();
        $this->invitations = new ArrayCollection();
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
     * @return mixed
     */
    public function getInvitations()
    {
        return $this->invitations;
    }

    /**
     * @param mixed $invitations
     */
    public function setInvitations($invitations)
    {
        $this->invitations = $invitations;
    }

    /**
     * @return mixed
     */
    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * @param mixed $invitation
     */
    public function setInvitation($invitation)
    {
        $this->invitation = $invitation;
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
}