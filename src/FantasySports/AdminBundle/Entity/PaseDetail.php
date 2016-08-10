<?php

namespace FantasySports\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PaseDetail
 *
 * @ORM\Table(name="pase_detail")
 * @ORM\Entity(repositoryClass="FantasySports\AdminBundle\Repository\PaseDetailRepository")
 */
class PaseDetail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="home_score", type="integer", nullable=true)
     */
    private $homeScore;

    /**
     * @var int
     *
     * @ORM\Column(name="away_score", type="integer", nullable=true)
     */
    private $awayScore;

    /**
     * @var int
     *
     * @ORM\Column(name="selection", type="integer", nullable=true)
     */
    private $selection;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Pase", inversedBy="paseDetails")
     * @ORM\JoinColumn(name="pase_id", referencedColumnName="id")
     **/
    private $pase;

    /**
     * @ORM\ManyToOne(targetEntity="SportMatch")
     * @ORM\JoinColumn(name="sport_match_id", referencedColumnName="id")
     */
    private $sportMatch;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getHomeScore()
    {
        return $this->homeScore;
    }

    /**
     * @param int $homeScore
     */
    public function setHomeScore($homeScore)
    {
        $this->homeScore = $homeScore;
    }

    /**
     * Set awayScore
     *
     * @param integer $awayScore
     *
     * @return PaseDetail
     */
    public function setAwayScore($awayScore)
    {
        $this->awayScore = $awayScore;

        return $this;
    }

    /**
     * Get awayScore
     *
     * @return int
     */
    public function getAwayScore()
    {
        return $this->awayScore;
    }

    /**
     * Set selection
     *
     * @param integer $selection
     *
     * @return PaseDetail
     */
    public function setSelection($selection)
    {
        $this->selection = $selection;

        return $this;
    }

    /**
     * Get selection
     *
     * @return int
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PaseDetail
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
     * @return mixed
     */
    public function getPase()
    {
        return $this->pase;
    }

    /**
     * @param mixed $pase
     */
    public function setPase($pase)
    {
        $this->pase = $pase;
    }

    /**
     * @return mixed
     */
    public function getSportMatch()
    {
        return $this->sportMatch;
    }

    /**
     * @param mixed $sportMatch
     */
    public function setSportMatch($sportMatch)
    {
        $this->sportMatch = $sportMatch;
    }
}

