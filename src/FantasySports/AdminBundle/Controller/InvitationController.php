<?php

namespace FantasySports\AdminBundle\Controller;

use FantasySports\AdminBundle\Entity\Invitation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InvitationController extends Controller
{
    public function inviteAction()
    {
        return $this->render('@FantasySportsAdmin/Invitation/invite.html.twig');
    }

    public function sendAction()
    {
        /*$em = $this->getDoctrine()->getManager();
        $invitation = new Invitation();
        $invitation->send();
        $em->persist($invitation);*/

        return $this->redirectToRoute('fantasy_sports_admin_dashboard');
    }
}
