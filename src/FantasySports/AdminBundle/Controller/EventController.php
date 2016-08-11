<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EventController extends Controller
{
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $eventRepository = $em->getRepository('FantasySportsAdminBundle:Events');
        $events = $eventRepository->findAll();

        return $this->render('FantasySportsAdminBundle:Event:list.html.twig', array('events' => $events));
    }
}
