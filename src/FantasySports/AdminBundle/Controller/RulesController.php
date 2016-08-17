<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RulesController extends Controller
{
    public function indexAction()
    {
        return $this->render('FantasySportsAdminBundle:Rules:index.html.twig');
    }
}