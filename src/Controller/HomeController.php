<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\HomeType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_')]
class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'home')]
    public function index(SortieRepository $repo,ParticipantRepository $rep,Request $request): Response
    {
    $sorties=$repo->findAll();
    $form=$this->createForm(HomeType::class);
    $form->handleRequest($request);
    if($form->isSubmitted()&&$form->isValid()){
        $strDateDebut=$request->get('datedebut').' 00:00';
        $strDateFin=$request->get('datefin').' 00:00';

        $datedebut=\DateTime::createFromFormat('Y-m-d H:i',$strDateDebut);
        $datefin=\DateTime::createFromFormat('Y-m-d H:i',$strDateFin);
        $condition1=$request->get('condition1');
        if(!isset($condition1)){
            $condition1=false;
        }
        $condition2=$request->get('condition2');
        if(!isset($condition2)){
            $condition2=false;
        }
        $condition3=$request->get('condition3');
        if(!isset($condition3)){
            $condition3=false;
        }
        $condition4=$request->get('condition4');
        if(!isset($condition4)){
            $condition4=false;
        }
        $sorties = $repo->findByArgs($rep->findByEmail($this->getUser()->getUserIdentifier()), $form->get('idCampus')->getData(), $datedebut, $datefin, $request->get('nomsortie'), $condition1, $condition2, $condition3,$condition4);
    }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form'=>$form->createView(),
            'sorties'=>$sorties,
        ]);
    }


    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/email', name: 'email')]
    public function emailtest(MailerInterface $mailer,): Response
    {
        $email = (new TemplatedEmail())
            ->from(new Address('smtp@baptiste-coutant.fr', 'Sortir'))
            ->to('baptiste.coutant2021@campus-eni.fr')
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/test.html.twig');

        $mailer->send($email);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}

