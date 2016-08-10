<?php

namespace FantasySports\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TransactionController extends Controller
{
    public function listAction($user)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('@FantasySportsAdmin/Transaction/list.html.twig', ['transactions'=>$user->getWallet()->getTransactions()]);
    }
}