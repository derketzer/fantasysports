<?php

namespace FantasySports\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SportMatch
 *
 * @ORM\Table(name="sport_match")
 * @ORM\Entity(repositoryClass="FantasySports\AdminBundle\Repository\SportMatchRepository")
 */
class SportMatch
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
     * @ORM\ManyToOne(targetEntity="Phase")
     * @ORM\JoinColumn(name="pahse_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $phase;

    /**
     * @var int
     *
     * @ORM\Column(name="jornada", type="integer")
     */
    private $jornada;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="homeSportMatches")
     * @ORM\JoinColumn(name="home_team_id", referencedColumnName="id", onDelete="SET NULL")
     **/
    private $homeTeam;

    /**
     * @var int
     *
     * @ORM\Column(name="home_score", type="integer")
     */
    private $homeScore=0;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="awaySportMatches")
     * @ORM\JoinColumn(name="away_team_id", referencedColumnName="id", onDelete="SET NULL")
     **/
    private $awayTeam;

    /**
     * @var int
     *
     * @ORM\Column(name="away_score", type="integer")
     */
    private $awayScore=0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="match_date", type="datetime")
     */
    private $matchDate;

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
     * @return mixed
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * @param mixed $phase
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;
    }

    /**
     * @return mixed
     */
    public function getJornada()
    {
        return $this->jornada;
    }

    /**
     * @param mixed $jornada
     */
    public function setJornada($jornada)
    {
        $this->jornada = $jornada;
    }

    /**
     * Set homeScore
     *
     * @param integer $homeScore
     *
     * @return SportMatch
     */
    public function setHomeScore($homeScore)
    {
        $this->homeScore = $homeScore;

        return $this;
    }

    /**
     * Get homeScore
     *
     * @return int
     */
    public function getHomeScore()
    {
        return $this->homeScore;
    }

    /**
     * Set awayScore
     *
     * @param integer $awayScore
     *
     * @return SportMatch
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
     * Set matchDate
     *
     * @param \DateTime $matchDate
     *
     * @return SportMatch
     */
    public function setMatchDate($matchDate)
    {
        $this->matchDate = $matchDate;

        return $this;
    }

    /**
     * Get matchDate
     *
     * @return \DateTime
     */
    public function getMatchDate()
    {
        return $this->matchDate;
    }

    /**
     * @return mixed
     */
    public function getHomeTeam()
    {
        return $this->homeTeam;
    }

    /**
     * @param mixed $homeTeam
     */
    public function setHomeTeam($homeTeam)
    {
        $this->homeTeam = $homeTeam;
    }

    /**
     * @return mixed
     */
    public function getAwayTeam()
    {
        return $this->awayTeam;
    }

    /**
     * @param mixed $awayTeam
     */
    public function setAwayTeam($awayTeam)
    {
        $this->awayTeam = $awayTeam;
    }
}

