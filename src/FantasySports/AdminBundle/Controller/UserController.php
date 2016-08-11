<?php

namespace FantasySports\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction()
    {
        $userRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:User');
        $users = $userRepository->findAll();

        return $this->render('@FantasySportsAdmin/User/list.html.twig', ['users'=>$users]);
    }
}
