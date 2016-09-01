<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RankingController extends Controller
{
    public function rankingWeekAction()
    {
        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $rankingRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Ranking');
        $ranking = $rankingRepository->findBy(['type'=>1, 'week'=>$this->container->getParameter('week'), 'phase'=>$phase]);

        return $this->render('@FantasySportsAdmin/Ranking/week.html.twig', ['ranking'=>$ranking]);
    }

    public function rankingGeneralAction()
    {
        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $rankingRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Ranking');
        $ranking = $rankingRepository->findBy(['type'=>2, 'week'=>$this->container->getParameter('week'), 'phase'=>$phase]);

        return $this->render('@FantasySportsAdmin/Ranking/general.html.twig', ['ranking'=>$ranking]);
    }
}
