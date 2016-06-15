<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('FantasySportsAdminBundle:Index:dashboard.html.twig');
    }
}
