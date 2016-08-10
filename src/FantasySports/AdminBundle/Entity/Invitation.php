<?php

namespace FantasySports\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="string", length=256)
     *
     */
    protected $email;

    /**
     *
     * @ORM\Column(type="boolean")
     *
     */
    protected $sent = false;

    public function __construct()
    {
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function isSent()
    {
        return $this->sent;
    }

    public function send()
    {
        $this->sent = true;
    }
}