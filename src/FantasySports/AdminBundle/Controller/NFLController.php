<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NFLController extends Controller
{
    public function resultsAction()
    {
        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');
        $matches = $sportMatchRespository->findBy(['phase'=>$phase->getId(), 'jornada'=>$this->container->getParameter('week')-1], ['matchDate'=>'ASC']);

        return $this->render('FantasySportsAdminBundle:NFL:results.html.twig', ['matches'=>$matches]);
    }
}
