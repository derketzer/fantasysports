<?php

namespace FantasySports\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $productCategoryRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:CartaCategory');
        $categories = $productCategoryRespository->findAll();

        $eventsRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Events');
        $events = $eventsRespository->findBy(Array(), Array('eventDate'=>'DESC'), 4);

        $newsRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:News');
        $news = $newsRespository->findBy(Array(), Array('newsDate'=>'DESC'), 4);

        return $this->render('FantasySportsSiteBundle::index.html.twig', Array(
            'categories' => $categories,
            'events' => $events,
            'news' => $news
        ));
    }
}