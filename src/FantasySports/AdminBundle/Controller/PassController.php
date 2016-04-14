<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PassController extends Controller
{
    public function addAction()
    {
        return $this->render('FantasySportsAdminBundle:Pass:pass.html.twig');
    }
    
    public function saveAction()
    {
        $qrUrl = 'https://s3.amazonaws.com/fantasysports.mx/passes/villano-chelero/1/villano.pkpass';

        return $this->render('FantasySportsAdminBundle:Pass:save.html.twig', Array('qrUrl'=>$qrUrl));
    }
}
