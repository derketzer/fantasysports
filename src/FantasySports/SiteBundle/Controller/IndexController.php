<?php

namespace FantasySports\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        return $this->render('FantasySportsSiteBundle::index.html.twig');
    }
}