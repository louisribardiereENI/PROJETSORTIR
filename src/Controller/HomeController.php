<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\HomeType;
use App\Repository\EtatRepository;
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
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/', name: 'app_')]
class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'home')]
    public function index(SortieRepository $repo,ParticipantRepository $rep,EtatRepository $repEtat,Request $request): Response
    {
    $sorties=$repo->findAll();
    foreach ($sorties as $sortie){
        $actual=new \DateTime();
        $actual->setTimezone(new \DateTimeZone("Europe/Paris"));
        $libelle=null;
        if($sortie->getIdEtat()->getLibelle()!="Annulée") {

            //Transfert entre DateTime Symfony vers DateTime PHP
            $dateHeureDebut=new \DateTime();
            $dateHeureDebut->setTimestamp($sortie->getDateHeureDebut()->getTimestamp());
            $dateHeureDebut->modify('-1 hours');

            //Transfert entre DateTime Symfony vers DateTime PHP
            $datesortie=new \DateTime();
            $datesortie->setTimestamp($dateHeureDebut->getTimestamp());
            $datesortie->modify('+'.$sortie->getDuree().' minutes');

            if ($sortie->getNbInscriptionsMax() == count($sortie->getIdParticipant()) || $sortie->getDateLimiteInscription() < $actual) {
                $libelle = "Clôturée";
            }
            if ($datesortie < $actual) {
                $libelle = "Passée";
            }

            if($dateHeureDebut->getTimestamp()<=$actual->getTimestamp()&&$actual->getTimestamp()<=$datesortie->getTimestamp()){
               $libelle="Activité en cours";
            }
            if($libelle!=null) {
                $sortie->setIdEtat($repEtat->findByLibelle($libelle));
                $repo->save($sortie,true);
            }
        }

    }
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

