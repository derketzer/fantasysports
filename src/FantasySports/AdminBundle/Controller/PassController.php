<?php

namespace FantasySports\AdminBundle\Controller;

use Aws\S3\S3Client;
use FantasySports\AdminBundle\Entity\Pase;
use FantasySports\AdminBundle\Entity\PaseDetail;
use FantasySports\AdminBundle\Entity\PaseDevice;
use FantasySports\AdminBundle\Entity\WalletTransaction;
use PKPass\PKPass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PassController extends Controller
{
    public function addAction(Request $request)
    {
        $week = $this->container->getParameter('week');

        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(['name'=>'week']);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $passRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Pase');
        $pass = $passRepository->findOneBy(['phase'=>$phase, 'jornada'=>$week, 'user'=>$user]);

        $matches = null;
        $passExist = false;
        if(empty($pass)){
            $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');
            $matches = $sportMatchRespository->findBy(['phase'=>$phase->getId(), 'jornada'=>$week], ['matchDate'=>'ASC']);
        }else{
            $passExist = true;
        }

        $vars = [
            'matches' => $matches,
            'phase' => $phase->getId(),
            'jornada' => $week,
            'passExist' => $passExist
        ];

        return $this->render('FantasySportsAdminBundle:Pass:pass.html.twig', $vars);
    }
    
    public function saveAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $wallet = $user->getWallet();

        if($wallet->getBalance() <= 0){
            return $this->redirectToRoute('fantasy_sports_admin_dashboard');
        }

        $data = $request->request->all();

        $passRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Pase');
        $pass = $passRepository->findOneBy(['phase'=>$data['phase'], 'jornada'=>$data['jornada'], 'user'=>$user]);
        if(!empty($pass)){
            return $this->redirectToRoute('fantasy_sports_admin_pass_list');
        }

        $path = $this->generateQuinielaPass($data);

        $qrUrl = 'https://s3.amazonaws.com/fantasysports.mx/'.$path;

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $wallet = $user->getWallet();
        $wallet->setBalance($wallet->getBalance()-1);
        $em->persist($wallet);

        $walletTransaction = new WalletTransaction();
        $walletTransaction->setAmount(-1);
        $walletTransaction->setCreatedAt(new \DateTime());
        $walletTransaction->setWallet($wallet);
        $walletTransaction->setUser($user);
        $em->persist($walletTransaction);

        $em->flush();

        //return $this->render('FantasySportsAdminBundle:Pass:save.html.twig', Array('qrUrl'=>$qrUrl));
        return $this->redirectToRoute('fantasy_sports_admin_pass_list');
    }

    private function generateQuinielaPass($data)
    {
        $pass = new PKPass();

        $mailText = '';

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $pase = new Pase();
        $pase->setType(2);
        $pase->setStatus(0);
        $pase->setCreatedAt(new \DateTime());
        $pase->setUser($user);

        $phaseRepository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRepository->findOneBy(['id'=>$data['phase']]);
        $pase->setJornada($data['jornada']);
        $pase->setPhase($phase);

        $mailText .= "¡Hola ".$user->getUsername()."!<br /><br />";
        $mailText .= "Gracias por llenar tu quiniela. Tus selecciones para la jornada ".$data['jornada']." son:<br /><br />";

        srand($this->make_seed());

        $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');

        $pass->setCertificate($this->container->get('kernel')->getRootDir().'/VillanoChelero.p12');
        $pass->setCertificatePassword($this->container->getParameter('pass_pass'));
        $pass->setWWDRcertPath($this->container->get('kernel')->getRootDir().'/AppleWWDRCA.pem');

        $firstMatchDate = 0;
        $lastMatchDate = 0;
        $matchesBackField = '';
        foreach($data['matches'] as $matchId=>$pronostico){
            $match = $sportMatchRespository->findOneBy(Array('id'=>$matchId));

            $paseDetail = new PaseDetail();
            $paseDetail->setSportMatch($match);
            $paseDetail->setCreatedAt(new \DateTime());

            if(is_array($pronostico)) {
                $homeScore = $pronostico['home'];
                $awayScore = $pronostico['away'];

                $paseDetail->setAwayScore($awayScore);
                $paseDetail->setHomeScore($homeScore);
            }else{
                $paseDetail->setSelection($pronostico);
            }

            $paseDetail->setPase($pase);
            $pase->addPaseDetail($paseDetail);

            $home = '';
            $none = ' - ';
            $away = '';
            if(!is_array($pronostico)) {
                switch ($pronostico) {
                    case 0:
                        $home = 'X ';
                        break;

                    case 1:
                        $none = ' X ';
                        break;

                    case 2:
                        $away = ' X';
                        break;
                }

                $matchesBackField .= $home.$match->getHomeTeam()->getShortName().$none.$match->getAwayTeam()->getShortName().$away."\n";

                $mailText .= $home.$match->getHomeTeam()->getShortName().$none.$match->getAwayTeam()->getShortName().$away."<br />";
            }else{
                $matchesBackField .= $match->getHomeTeam()->getShortName().' '.$homeScore.' - '.$awayScore.' '.$match->getAwayTeam()->getShortName()."\n";

                $mailText .= "<br />".$match->getHomeTeam()->getShortName().' '.$homeScore.' - '.$awayScore.' '.$match->getAwayTeam()->getShortName()."<br /><br />";
            }

            if($lastMatchDate < $match->getMatchDate()->getTimestamp())
                $lastMatchDate = $match->getMatchDate()->getTimestamp();

            if($firstMatchDate > $match->getMatchDate()->getTimestamp())
                $firstMatchDate = $match->getMatchDate()->getTimestamp();
        }

        $barcode = $this->generateRandomString();
        $serialNumber = $this->generateRandomString();
        $relevantDate = date('Y-m-d', $firstMatchDate)."T".date('H:i', $firstMatchDate)."-06:00";
        $couponLabel = date('d \d\e F, Y \@ H:i', $lastMatchDate);
        $couponValue = 'Quiniela';
        $expirationDate = date('Y-m-d', $lastMatchDate+3600*24*7)."T".date('H:i', $lastMatchDate+3600*24*7)."-06:00";
        $createdAt = date('d.m.y');

        $pase->setBarcode($barcode);
        $pase->setSerialNumber($serialNumber);

        $passData = [
            'formatVersion'       => 1,
            'description'         => 'Quiniela del Villano Chelero',
            'organizationName'    => 'El Villano Chelero',
            'passTypeIdentifier'  => $this->container->getParameter('apple_pass_identifier'),
            'serialNumber'        => $serialNumber,
            'teamIdentifier'      => $this->container->getParameter('apple_team'),
            "webServiceURL"       => 'http://villano-fantasy.com/pass/register',
            "authenticationToken" => "vxwxd7J8AlNNFPS8k0a0FfUFtq0ewzFdc",
            "barcode" => [
                "message" => $barcode,
                "format" => "PKBarcodeFormatCode128",
                "messageEncoding" => "iso-8859-1"
            ],
            "relevantDate" => $relevantDate,
            "logoText" => "El Villano Chelero",
            "foregroundColor" => "rgb(238, 17, 48)",
            "backgroundColor" => "rgb(250, 150, 36)",
            "coupon" => [
                "primaryFields" => [
                    [
                        "key" => "offer",
                        "label" => $couponLabel,
                        "value" => $couponValue
                    ]
                ],
                "auxiliaryFields" => [
                    [
                        "key" => "status",
                        "label" => "Status",
                        "value" => "Pendiente"
                    ],
                    [
                        "key" => "expires",
                        "label" => "Expira el",
                        "value" => $expirationDate,
                        "isRelative" => true,
                        "dateStyle" => "PKDateStyleShort"
                    ],
                    [
                        "key" => "created-at",
                        "label" => "Creado el",
                        "value" => $createdAt
                    ]
                ],
                "backFields" => [
                    [
                        "key" => "matches",
                        "label" => "Quiniela",
                        "value" => $matchesBackField
                    ],
                    [
                        "key" => "valid-for",
                        "label" => "Válido por",
                        "value" => "1 botella (Azul, Bacardi o Gotland)"
                    ],
                    [
                        "key" => "terms",
                        "label" => "Términos y condiciones",
                        "value" => "Válido hasta la fecha mostrada en el cupón."
                    ]
                ]
            ]
        ];

        $pass->setJSON(json_encode($passData));

        $pass->addFile($this->container->get('kernel')->getRootDir().'/Resources/pass/icon.png');
        $pass->addFile($this->container->get('kernel')->getRootDir().'/Resources/pass/icon@2x.png');
        $pass->addFile($this->container->get('kernel')->getRootDir().'/Resources/pass/logo.png');

        if ($pass->checkError($error) == true) {
            exit('An error occured: ' . $error);
        }

        $result = $pass->create(false);

        if ($result == false) {
            echo $pass->getError();
            exit();
        }

        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => [
                'key'    => $this->container->getParameter('aws_key'),
                'secret' => $this->container->getParameter('aws_secret')
            ]
        ]);

        $path = 'passes/villano-chelero/'.$user->getId().'/'.time().'.pkpass';

        $s3Client->putObject([
            'Bucket' => 'fantasysports.mx',
            'Key'    => $path,
            'ACL'    => 'public-read',
            'Body'   => $result,
            'ContentType' => 'application/vnd.apple.pkpass'
        ]);

        $em->persist($pase);
        $em->flush();

        $passUrl = 'https://s3.amazonaws.com/fantasysports.mx/'.$path;

        $mailText .= "<br />Gracias por participar. Si resultas ganador, estarás recibiendo un correo de confirmación que deberás de presentar para hacer válido tu premio.<br />";
        $mailText .= "<br />Si tienes alguna duda, envíanos un correo a <a href=\"mailto:staff@villano-fantasy.com\">staff@villano-fantasy.com</a>.<br />";
        //$mailText .= "<br />Para descargar tu pase para Apple Wallet puedes hacerlo  <a href=\"".$passUrl."\">aquí</a>.<br />";
        $mailText .= "<br />Saludos,<br />Villano Fantasy Staff";

        $message = \Swift_Message::newInstance()
            ->setSubject('Tu quiniela en Villano Fantasy!')
            ->setFrom('noreply@villano-fantasy.com')
            ->setTo($user->getEmail())
            ->setBody(
                $mailText,
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);

        return $path;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function make_seed(){
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }

    public function registerAction(Request $request, $deviceId, $passId, $serialNumber)
    {
        $requestBody = json_decode($request->getContent(), true);
        $authHeader = str_replace('ApplePass ', '', $request->headers->get('Authorization'));

        $paseDevice = new PaseDevice();
        $paseDevice->setCreatedAt(new \DateTime());
        $paseDevice->setAuthToken($authHeader);
        $paseDevice->setDeviceId($deviceId);
        $paseDevice->setPassId($passId);
        $paseDevice->setPushToken($requestBody['pushToken']);
        $paseDevice->setSerialNumber($serialNumber);

        $em = $this->getDoctrine()->getManager();
        $em->persist($paseDevice);
        $em->flush();

        return new Response('', 200);
    }

    public function deregisterAction($deviceId, $passId, $serialNumber)
    {
        $paseDeviceRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:PaseDevice');
        $paseDevice = $paseDeviceRespository->findOneBy(['deviceId'=>$deviceId, 'passId'=>$passId, 'serialNumber'=>$serialNumber]);

        if(!empty($paseDevice)) {
            $this->getDoctrine()->getManager()->remove($paseDevice);
            $this->getDoctrine()->getManager()->flush();
        }
        return new Response('', 200);
    }

    public function listAction($status)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $passRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Pase');

        if($status == -1)
            $passes = $passRespository->findBy(['user'=>$user], ['createdAt'=>'DESC']);
        else
            $passes = $passRespository->findBy(['user'=>$user, 'status'=>$status], ['createdAt'=>'DESC']);

        return $this->render('FantasySportsAdminBundle:Pass:list.html.twig', ['passes'=>$passes]);
    }

    public function detailAction($passId)
    {
        if($passId == 0)
            return $this->redirectToRoute('fantasy_sports_admin_dashboard');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $passRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Pase');
        $pass = $passRespository->findOneBy(['id'=>$passId]);

        if($pass->getUser()->getId() != $user->getId())
            return $this->redirectToRoute('fantasy_sports_admin_dashboard');

        return $this->render('FantasySportsAdminBundle:Pass:detail.html.twig', ['pass'=>$pass]);
    }
}
