<?php

namespace FantasySports\AdminBundle\Listener;

use Doctrine\ORM\EntityManager;
use FantasySports\AdminBundle\Entity\Wallet;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FosUserListener implements EventSubscriberInterface
{
    private $em;
    protected $container;

    public function __construct(EntityManager $manager, Container $container)
    {
        $this->em = $manager;
        $this->container = $container;
    }

    /**
     *
     * {@inheritDoc}
     *
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        );
    }

    /**
     *
     * Triggered when FOSUserEvents::REGISTRATION_COMPLETED is caught.
     *
     * {@inheritDoc}
     *
     */
    public function onRegistrationCompleted(UserEvent $userEvent)
    {
        $user = $userEvent->getUser();
        $parameters = $userEvent->getRequest()->get('fos_user_registration_form');
        $invitationCode = $parameters['invitation'];
        try{
            $wallet = new Wallet();
            $wallet->setBalance($this->container->getParameter('default_balance'));
            $wallet->setUser($user);
            $wallet->setCreatedAt(new \DateTime());
            $wallet->setUpdatedAt(new \DateTime());
            $this->em->persist($wallet);

            $invitationRepository = $this->em->getRepository('FantasySportsAdminBundle:Invitation');
            $invitation = $invitationRepository->findOneBy(['code'=>$invitationCode]);
            $invitation->setAccepted(true);
            $invitation->setAcceptedAt(new \DateTime());
            $this->em->persist($invitation);

            $this->em->flush();
        }catch(\Exception $e){
        }
    }
}