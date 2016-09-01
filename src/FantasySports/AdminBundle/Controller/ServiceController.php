<?php

namespace FantasySports\AdminBundle\Controller;

use FantasySports\AdminBundle\Entity\PaseDetail;
use FantasySports\AdminBundle\Entity\Ranking;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    public function calificaAction()
    {
        $em = $this->getDoctrine()->getManager();

        $phaseRespository = $em->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $paseRepository = $em->getRepository('FantasySportsAdminBundle:Pase');
        $quinielas = $paseRepository->findBy(['jornada'=>$this->container->getParameter('week'), 'phase'=>$phase]);

        $sportMatchRepository = $em->getRepository('FantasySportsAdminBundle:SportMatch');
        $sportMatches = $sportMatchRepository->findBy(['jornada'=>$this->container->getParameter('week'), 'phase'=>$phase]);

        if(!empty($quinielas)){
            foreach ($quinielas as $quiniela){
                $points = 0;
                $matchesPoints = 0;

                foreach($quiniela->getPaseDetails() as $paseDetail){
                    if(in_array($paseDetail->getSportMatch(), $sportMatches)) {
                        $homeFinalScore = $paseDetail->getSportMatch()->getHomeScore() + $paseDetail->getSportMatch()->getAwayOdds();
                        $awayFinalScore = $paseDetail->getSportMatch()->getAwayScore() + $paseDetail->getSportMatch()->getHomeOdds();
                        $matchScoreDifference = $homeFinalScore - $awayFinalScore;

                        if (!empty($paseDetail->getHomeScore())) {
                            /*
                             * Seleccion por marcador
                             */

                            // Diferencia del marcador del usuario
                            $userScoreDifference = $paseDetail->getHomeScore() - $paseDetail->getAwayScore();

                            // Si el marcador es mayor a 0, selecciono al equipo local
                            if($userScoreDifference > 0 && $matchScoreDifference > 0 && $userScoreDifference >= $matchScoreDifference){
                                $points++;
                                $matchesPoints += $userScoreDifference - $matchScoreDifference;
                            }

                            // Si el marcador es menor a 0, selecciono al equipo visitante
                            if($userScoreDifference < 0 && $matchScoreDifference < 0 && $userScoreDifference <= $matchScoreDifference){
                                $points++;
                                $matchesPoints += $userScoreDifference - $matchScoreDifference;
                            }

                            // Si el marcador es igual a 0, selecciono un empate
                            if($userScoreDifference == 0 && $matchScoreDifference == 0){
                                $points++;
                                $matchesPoints += $userScoreDifference - $matchScoreDifference;
                            }
                        } else {
                            switch($paseDetail->getSelection()){
                                // Local
                                case 0:
                                    if($matchScoreDifference > 0){
                                        $points++;
                                    }
                                    break;

                                // Empate
                                case 1:
                                    if($matchScoreDifference == 0){
                                        $points++;
                                    }
                                    break;

                                // Visitante
                                case 2:
                                    if($matchScoreDifference < 0){
                                        $points++;
                                    }
                                    break;
                            }
                        }
                    }
                }

                $ranking = new Ranking();
                $ranking->setType(1); // Ranking semanal
                $ranking->setCreatedAt(new \DateTime());
                $ranking->setPhase($phase);
                $ranking->setWeek($this->container->getParameter('week'));
                $ranking->setUser($quiniela->getUser());
                $ranking->setPoints($points);
                $ranking->setMatchesPoints($matchesPoints);
                $ranking->setPass($quiniela);

                $em->persist($ranking);
            }

            try{
                $em->flush();
            }catch (\Exception $e){

            }
        }

        return new Response('Exito!');
    }

    public function ordenaAction()
    {
        $em = $this->getDoctrine()->getManager();

        $phaseRespository = $em->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $rankingRepository = $em->getRepository('FantasySportsAdminBundle:Ranking');
        $rankings = $rankingRepository->findBy(['week'=>$this->container->getParameter('week'), 'phase'=>$phase, 'type'=>1], ['points'=>'desc', 'matchesPoints'=>'desc']);

        if(!empty($rankings)){
            $sportMatchRepository = $em->getRepository('FantasySportsAdminBundle:SportMatch');
            $sportMatches = $sportMatchRepository->findBy(['jornada'=>$this->container->getParameter('week'), 'phase'=>$phase]);

            if(!empty($sportMatches)){
                $position = 1;
                $currentPoints = -1;
                $toOrder = [];
                foreach ($rankings as $ranking){
                    if($currentPoints != $ranking->getPoints()){
                        $currentPoints = $ranking->getPoints();
                        usort($toOrder, [$this, 'orderByMatchPoints']);
                        usort($toOrder, [$this, 'orderByDate']);

                        foreach ($toOrder as $rank){
                            $rank->setPosition($position);
                            $em->persist($rank);
                            $position++;
                        }

                        $toOrder = [];
                    }

                    $toOrder[] = $ranking;
                }

                if(!empty($toOrder)){
                    usort($toOrder, [$this, 'orderByMatchPoints']);
                    usort($toOrder, [$this, 'orderByDate']);

                    foreach ($toOrder as $rank){
                        $rank->setPosition($position);
                        $em->persist($rank);
                        $position++;
                    }
                }

                $em->flush();
            }
        }

        return new Response('Exito!');
    }

    private function orderByMatchPoints($a, $b)
    {
        if($a->getMatchesPoints() == $b->getMatchesPoints())
            return 0;

        return $a->getMatchesPoints() < $b->getMatchesPoints() ? -1 : 1;
    }

    private function orderByDate($a, $b)
    {
        if($a->getPass()->getCreatedAt()->getTimestamp() == $b->getPass()->getCreatedAt()->getTimestamp())
            return 0;

        return $a->getPass()->getCreatedAt()->getTimestamp() < $b->getPass()->getCreatedAt()->getTimestamp() ? -1 : 1;
    }

    public function notificaAction()
    {
        $em = $this->getDoctrine()->getManager();

        $phaseRespository = $em->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $rankingRepository = $em->getRepository('FantasySportsAdminBundle:Ranking');
        $ganadores = $rankingRepository->findBy(['week'=>$this->container->getParameter('week'), 'phase'=>$phase, 'type'=>1], ['position'=>'asc'], $this->container->getParameter('week_winners'));

        if(!empty($ganadores)){
            foreach ($ganadores as $ganador){
                $emailText = "¡Hola ".$ganador->getUser()->getUsername()."!<br /><br />";
                $emailText .= "Acabas de ganar la quiniela de la jornada ".$this->container->getParameter('week').".<br /><br />";

                $validoAl = (new \DateTime())->modify('+7 days');
                $emailText .= "Para hacer válido tu premio necesitas presentar este correo antes del ".$validoAl->format("d-m-Y").".<br /><br />";
                $emailText .= "Felicidades de parte del Villano Fantasy Staff";

                $message = \Swift_Message::newInstance()
                    ->setSubject('Felicidades, ganaste la Villano Fantasy!')
                    ->setFrom('noreply@villano-fantasy.com', 'Villano Fantasy')
                    ->setTo($ganador->getUser()->getEmail())
                    ->setBody(
                        $emailText,
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
            }
        }

        return new Response('Exito!');
    }
}
