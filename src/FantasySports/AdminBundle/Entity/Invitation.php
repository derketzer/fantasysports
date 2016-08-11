<?php

namespace FantasySports\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity */
class Invitation
{
    /**
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=6)
     *
     */
    protected $code;

    /**
     *
     * @ORM\Column(type="string", length=255, unique=true)
     *
     */
    protected $email;

    /**
     *
     * @ORM\Column(type="boolean")
     *
     */
    protected $sent = false;

    /**
     *
     * @ORM\Column(type="boolean")
     *
     */
    protected $accepted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="accepted_at", type="datetime", nullable=true)
     */
    private $acceptedAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="invitations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $from;

    public function __construct()
    {
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
    }

    public function isSent()
    {
        return $this->sent;
    }
    public function send()
    {
        $this->sent = true;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param mixed $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * @return mixed
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * @param mixed $accepted
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getAcceptedAt()
    {
        return $this->acceptedAt;
    }

    /**
     * @param \DateTime $acceptedAt
     */
    public function setAcceptedAt($acceptedAt)
    {
        $this->acceptedAt = $acceptedAt;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }
}