<?php

namespace FantasySports\AdminBundle\Controller;

use FantasySports\AdminBundle\Entity\Invitation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvitationController extends Controller
{
    public function inviteAction(Request $request)
    {
        $error = null;
        if(!empty($request->query->get('error')))
            $error = $request->query->get('error');

        $success = null;
        if(!empty($request->query->get('success')))
            $success = $request->query->get('success');

        return $this->render('@FantasySportsAdmin/Invitation/invite.html.twig', ['error' => $error,
            'success' => $success
        ]);
    }

    public function listAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('@FantasySportsAdmin/Invitation/list.html.twig', ['invitations' => $user->getInvitations()]);
    }

    public function sendAction(Request $request)
    {
        $email = $request->request->get('email');

        $validator = $this->get('validator');
        $constraints = array(
            new Email(),
            new NotBlank()
        );

        $error = $validator->validate($email, $constraints);

        if(count($error) > 0)
            return $this->redirectToRoute('fantasy_sports_admin_transaction_invite', ['error'=>(string)$error]);

        try {
            $em = $this->getDoctrine()->getManager();

            $invitationRespository = $em->getRepository('FantasySportsAdminBundle:Invitation');
            $invitationTemp = $invitationRespository->findOneBy(Array('email'=>$email));

            if(!empty($invitationTemp))
                return $this->redirectToRoute('fantasy_sports_admin_transaction_invite', ['error'=>'Ya enviaste una invitación a ese correo!']);

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $invitation = new Invitation();
            $invitation->send();
            $invitation->setEmail($email);
            $invitation->setAccepted(false);
            $invitation->setCreatedAt(new \DateTime());
            $invitation->setFrom($user);
            $em->persist($invitation);

            $message = \Swift_Message::newInstance()
                ->setSubject('Invitación del Villano chelero')
                ->setFrom('noreply@fantasysports.mx')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        '@FantasySportsAdmin/Email/invitation.html.twig',
                        ['name' => $user->getUsername(), 'code' => $invitation->getCode()]
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);

            $em->flush();
        } catch(\Exception $e){
            echo $e->getMessage();
            exit();
        }

        return $this->redirectToRoute('fantasy_sports_admin_transaction_invite', ['success'=>'La invitación ha sido enviada!']);
    }
}
