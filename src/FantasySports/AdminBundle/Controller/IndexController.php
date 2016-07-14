<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function dashboardAction()
    {
        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');
        $matches = $sportMatchRespository->findBy(Array('phase'=>$phase->getId(), 'jornada'=>1), Array('matchDate'=>'ASC'));

        return $this->render('FantasySportsAdminBundle:Index:dashboard.html.twig', Array(
            'pasesAdquiridos' => 0,
            'pasesGanados' => 0,
            'pasesPendientes' => 0,
            'pasesPerdidos' => 0,
            'matches' => $matches
        ));
    }
}
