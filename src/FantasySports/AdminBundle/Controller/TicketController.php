<?php

namespace FantasySports\AdminBundle\Controller;

use FantasySports\AdminBundle\Entity\Ticket;
use FantasySports\AdminBundle\Entity\TicketAnswer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TicketController extends Controller
{
    public function newAction()
    {
        return $this->render('@FantasySportsAdmin/Ticket/ticket.html.twig');
    }

    public function saveAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $data = $request->request->all();

        $ticket = new Ticket();
        $ticket->setSummary($data['summary']);
        $ticket->setDescription($data['description']);
        $ticket->setSolved(false);
        $ticket->setUser($user);

        $this->getDoctrine()->getManager()->persist($ticket);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('fantasy_sports_ticket_list');
    }

    public function viewAction($ticketId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $ticketRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Ticket');
        $ticket = $ticketRepository->findOneBy(['user'=>$user, 'id'=>$ticketId]);

        if(empty($ticket))
            return $this->redirectToRoute('fantasy_sports_ticket_list');

        return $this->render('@FantasySportsAdmin/Ticket/view.html.twig', ['ticket'=>$ticket]);
    }

    public function listAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $ticketRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Ticket');
        $tickets = $ticketRepository->findBy(['user'=>$user]);

        return $this->render('@FantasySportsAdmin/Ticket/list.html.twig', ['tickets'=>$tickets]);
    }

    public function answerSaveAction($ticketId, Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $ticketRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Ticket');

        if($user->hasRole('ROLE_ADMIN'))
            $ticket = $ticketRepository->findOneBy(['id'=>$ticketId]);
        else
            $ticket = $ticketRepository->findOneBy(['id'=>$ticketId, 'user'=>$user]);

        if(empty($ticket))
            return $this->redirectToRoute('fantasy_sports_ticket_list');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $data = $request->request->all();

        $answer = new TicketAnswer();
        $answer->setUser($user);
        $answer->setAnswer($data['answer']);
        $answer->setTicket($ticket);

        $this->getDoctrine()->getManager()->persist($answer);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('fantasy_sports_ticket_list');
    }
}