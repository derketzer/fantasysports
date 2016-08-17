<?php

namespace FantasySports\AdminBundle\Controller;

use FantasySports\AdminBundle\Entity\WalletTransaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CouponController extends Controller
{
    public function couponAction()
    {
        return $this->render('@FantasySportsAdmin/Coupon/add.html.twig');
    }

    public function addAction(Request $request)
    {
        $couponString = $request->request->get('coupon');
        if(empty($couponString))
            return $this->redirectToRoute('fantasy_sports_admin_coupon');

        $em = $this->getDoctrine()->getManager();

        $userRespository = $em->getRepository('FantasySportsAdminBundle:User');

        $couponRespository = $em->getRepository('FantasySportsAdminBundle:Coupon');
        $couponTemp = $couponRespository->findOneBy(Array('coupon'=>$couponString, 'used'=>false));

        if(empty($couponTemp)){
            return $this->redirectToRoute('fantasy_sports_admin_coupon', ['error'=>'El cupón no existe o ya fue usado!']);
        }

        $couponTemp->setUsed(true);
        $em->persist($couponTemp);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $wallet = $user->getWallet();
        $wallet->setBalance(16);

        $em->persist($wallet);

        $walletTransaction = new WalletTransaction();
        $walletTransaction->setAmount(16);
        $walletTransaction->setCreatedAt(new \DateTime());
        $walletTransaction->setWallet($wallet);

        $adminUser = $userRespository->findOneBy(['id'=>1]);
        $walletTransaction->setUser($adminUser);

        $em->persist($walletTransaction);

        $em->flush();

        return $this->redirectToRoute('fantasy_sports_admin_dashboard');
    }
}
