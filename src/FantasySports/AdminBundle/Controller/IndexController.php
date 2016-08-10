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

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $passRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Pase');

        $query = $passRespository->createQueryBuilder('p')
            ->select('count(p.id) AS conteo, p.status')
            ->where("p.user = :user")
            ->groupBy('p.status')
            ->setParameter('user', $user)
            ->getQuery();
        $passes = $query->getResult();

        $passesCount = [0, 0, 0];
        $passesTotal = 0;
        foreach ($passes as $pass){
            $passesCount[$pass['status']] = $pass['conteo'];
            $passesTotal += $pass['conteo'];
        }

        return $this->render('FantasySportsAdminBundle:Index:dashboard.html.twig', Array(
            'pasesAdquiridos' => $passesTotal,
            'pasesGanados' => $passesCount[1],
            'pasesPendientes' => $passesCount[0],
            'pasesPerdidos' => $passesCount[2],
            'matches' => $matches
        ));
    }
}
