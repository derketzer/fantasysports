<?php

namespace FantasySports\AdminBundle\Controller;

use Aws\S3\S3Client;
use FantasySports\AdminBundle\Entity\Pase;
use FantasySports\AdminBundle\Entity\PaseDetail;
use PKPass\PKPass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PassController extends Controller
{
    public function addAction(Request $request)
    {
        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');
        $matches = $sportMatchRespository->findBy(Array('phase'=>$phase->getId(), 'jornada'=>1), Array('matchDate'=>'ASC'));

        $productRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:CartaProduct');
        $products = $productRespository->findAll();

        $vars = Array(
            'matches' => $matches,
            'products' => $products,
            'phase' => $phase->getId(),
            'jornada' => 1
        );

        if(!empty($request->get('match_id')))
            $vars['match_id'] = $request->get('match_id');

        return $this->render('FantasySportsAdminBundle:Pass:pass.html.twig', $vars);
    }
    
    public function saveAction(Request $request)
    {
        $data = $request->request->all();

        if($data['pass-type'] == 1)
            $path = $this->generateQuinielaPass($data);
        else if($data['pass-type'] == 2)
            $path = $this->generateMatchPass($data);

        $qrUrl = 'https://s3.amazonaws.com/fantasysports.mx/'.$path;

        return $this->render('FantasySportsAdminBundle:Pass:save.html.twig', Array('qrUrl'=>$qrUrl));
    }

    private function generateQuinielaPass($data)
    {
        $pass = new PKPass();

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $pase = new Pase();
        $pase->setType(2);
        $pase->setStatus(0);
        $pase->setCreatedAt(new \DateTime());
        $pase->setUser($user);

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
            $paseDetail->setSelection($pronostico);
            $paseDetail->setPase($pase);
            $pase->addPaseDetail($paseDetail);

            $home = '';
            $none = ' - ';
            $away = '';
            switch ($pronostico){
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

            if($lastMatchDate < $match->getMatchDate()->getTimestamp())
                $lastMatchDate = $match->getMatchDate()->getTimestamp();

            if($firstMatchDate > $match->getMatchDate()->getTimestamp())
                $firstMatchDate = $match->getMatchDate()->getTimestamp();
        }

        $barcode = $this->generateRandomString();
        $relevantDate = date('Y-m-d', $firstMatchDate)."T".date('H:i', $firstMatchDate)."-06:00";
        $couponLabel = date('d \d\e F, Y \@ H:i', $lastMatchDate);
        $couponValue = 'Quiniela';
        $expirationDate = date('Y-m-d', $lastMatchDate+3600*24)."T".date('H:i', $lastMatchDate+3600*24)."-06:00";
        $createdAt = date('d.m.y');

        $passData = [
            'formatVersion'       => 1,
            'description'         => 'Quiniela del Villano Chelero',
            'organizationName'    => 'El Villano Chelero',
            'passTypeIdentifier'  => $this->container->getParameter('apple_pass_identifier'),
            'serialNumber'        => $this->generateRandomString(),
            'teamIdentifier'      => $this->container->getParameter('apple_team'),
            "webServiceURL"       => "https://fantasysports.mx/",
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
                        "key" => "expires",
                        "label" => "Expira el",
                        "value" => $expirationDate,
                        "isRelative" => true,
                        "dateStyle" => "PKDateStyleShort"
                    ],
                    [
                        "key" => "valid-for",
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
                        "value" => "1 botella"
                    ],
                    [
                        "key" => "terms",
                        "label" => "Términos y condiciones",
                        "value" => "Válido hasta 24 horas después del día del último partido de la jornada."
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

        if ($result == false) { // Create and output the PKPass
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

        return $path;
    }

    private function generateMatchPass($data)
    {
        $pass = new PKPass();

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $pase = new Pase();
        $pase->setType(1);
        $pase->setStatus(0);
        $pase->setCreatedAt(new \DateTime());
        $pase->setUser($user);

        srand($this->make_seed());

        $pass->setCertificate($this->container->get('kernel')->getRootDir().'/VillanoChelero.p12');
        $pass->setCertificatePassword($this->container->getParameter('pass_pass'));
        $pass->setWWDRcertPath($this->container->get('kernel')->getRootDir().'/AppleWWDRCA.pem');

        $pass_match = $data['pass-match'];
        $homeScore = $data['homeScore'];
        $awayScore = $data['awayScore'];

        $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');
        $match = $sportMatchRespository->findOneBy(Array('id'=>$pass_match));

        $paseDetail = new PaseDetail();
        $paseDetail->setSportMatch($match);
        $paseDetail->setCreatedAt(new \DateTime());
        $paseDetail->setAwayScore($awayScore);
        $paseDetail->setHomeScore($homeScore);
        $paseDetail->setPase($pase);
        $pase->addPaseDetail($paseDetail);

        $barcode = $this->generateRandomString();
        $relevantDate = date('Y-m-d', $match->getMatchDate()->getTimestamp())."T".date('H:i', $match->getMatchDate()->getTimestamp())."-06:00";
        $couponLabel = date('d \d\e F, Y \@ H:i', $match->getMatchDate()->getTimestamp());
        $couponValue = $match->getHomeTeam()->getShortName()." ".$homeScore." - ".$awayScore." ".$match->getAwayTeam()->getShortName();
        $expirationDate = date('Y-m-d', $match->getMatchDate()->getTimestamp()+3600*24)."T".date('H:i', $match->getMatchDate()->getTimestamp()+3600*24)."-06:00";
        $createdAt = date('d.m.y');

        $passData = [
            'formatVersion'       => 1,
            'description'         => 'Quiniela del Villano Chelero',
            'organizationName'    => 'El Villano Chelero',
            'passTypeIdentifier'  => $this->container->getParameter('apple_pass_identifier'),
            'serialNumber'        => $this->generateRandomString(),
            'teamIdentifier'      => $this->container->getParameter('apple_team'),
            "webServiceURL"       => "https://fantasysports.mx/",
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
                        "key" => "expires",
                        "label" => "Expira el",
                        "value" => $expirationDate,
                        "isRelative" => true,
                        "dateStyle" => "PKDateStyleShort"
                    ],
                    [
                        "key" => "valid-for",
                        "label" => "Creado el",
                        "value" => $createdAt
                    ]
                ],
                "backFields" => [
                    [
                        "key" => "terms",
                        "label" => "Términos y condiciones",
                        "value" => "Válido hasta 24 horas después del día del partido."
                    ],
                    [
                        "key" => "valid-for",
                        "label" => "Válido por",
                        "value" => "1 botella"
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

        if ($result == false) { // Create and output the PKPass
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
