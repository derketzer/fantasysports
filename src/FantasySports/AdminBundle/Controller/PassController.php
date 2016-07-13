<?php

namespace FantasySports\AdminBundle\Controller;

use Aws\S3\S3Client;
use PKPass\PKPass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PassController extends Controller
{
    public function addAction()
    {
        $phaseRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:Phase');
        $phase = $phaseRespository->findOneBy(Array('name'=>'week'));

        $sportMatchRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:SportMatch');
        $matches = $sportMatchRespository->findBy(Array('phase'=>$phase->getId(), 'jornada'=>1), Array('matchDate'=>'ASC'));

        $productRespository = $this->getDoctrine()->getRepository('FantasySportsAdminBundle:CartaProduct');
        $products = $productRespository->findAll();

        return $this->render('FantasySportsAdminBundle:Pass:pass.html.twig', Array(
            'matches'=>$matches,
            'products'=>$products,
            'phase'=>$phase->getId(),
            'jornada'=>1
        ));
    }
    
    public function saveAction(Request $request)
    {
        $data = $request->request->all();
        //var_dump($data);

        $path = $this->generatePass($data);
        $qrUrl = 'https://s3.amazonaws.com/fantasysports.mx/'.$path;

        return $this->render('FantasySportsAdminBundle:Pass:save.html.twig', Array('qrUrl'=>$qrUrl));
    }

    private function generatePass($data)
    {
        $pass = new PKPass();

        $pass->setCertificate($this->container->get('kernel')->getRootDir().'/VillanoChelero.p12');
        $pass->setCertificatePassword($this->container->getParameter('pass_pass'));
        $pass->setWWDRcertPath($this->container->get('kernel')->getRootDir().'/AppleWWDRCA.pem');

        $barcode = "123456789";
        $relevantDate = "2016-04-12T13:45+02:00";
        $couponLabel = "12 de Abril, 2016";
        $couponValue = "Cupon test";
        $expirationDate = "2013-04-13T10:00-05:00";
        $validFor = "1 botella gratis";

        $passData = [
            'formatVersion'       => 1,
            'description'         => 'Quiniela del Villano Chelero',
            'organizationName'    => 'El Villano Chelero',
            'passTypeIdentifier'  => $this->container->getParameter('apple_pass_identifier'),
            'serialNumber'        => 'E5982H-I2',
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
                        "label" => "Válido por",
                        "value" => $validFor
                    ]
                ],
                "backFields" => [
                    [
                        "key" => "terms",
                        "label" => "Términos y condiciones",
                        "value" => "Válido únicamente el día del partido"
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

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $path = 'passes/villano-chelero/'.$user->getId().'/'.time().'.pkpass';

        $s3Client->putObject([
            'Bucket' => 'fantasysports.mx',
            'Key'    => $path,
            'ACL'    => 'public-read',
            'Body'   => $result,
            'ContentType' => 'application/vnd.apple.pkpass'
        ]);

        return $path;
    }
}
