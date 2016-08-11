<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NewsController extends Controller
{
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $newsRepository = $em->getRepository('FantasySportsAdminBundle:News');
        $news = $newsRepository->findAll();

        return $this->render('@FantasySportsAdmin/News/list.html.twig', ['news'=>$news]);
    }
}
