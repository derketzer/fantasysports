<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function loginAction()
    {
        return $this->render('FantasySportsAdminBundle:Index:login.html.twig');
    }

    public function dashboardAction()
    {
        return $this->render('FantasySportsAdminBundle:Index:dashboard.html.twig');
    }
}
