<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\MigratingPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\equalTo;
use function PHPUnit\Framework\throwException;

#[Route('/participants', name: 'participant')]

class ParticipantController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function index(ParticipantRepository $repo): Response
    {
        $participants = $repo->findAll();
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
            'participants' => $participants,
        ]);

    }
    #[Route('/{id}/view', name: 'details')]
    public function detail(ParticipantRepository $repo,int $id): Response
    {
        $participant = $repo->find($id);
        return $this->render('participant/detail.html.twig', [
            'controller_name' => 'ParticipantController',
            'participant' =>$participant,
        ]);
    }
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request,ManagerRegistry $repo,int $id,UserPasswordHasherInterface $userPasswordHasher): ?Response
    {
        $participant = $repo->getManager()->find(Participant::class,$id);
       if(($this->getUser()->getUserIdentifier()==$participant->getEmail())||$this->getUser()->getRoles()[0]=="ADMIN") {
           $form = $this->createForm(ParticipantType::class, $participant);
           $form->handleRequest($request);
           if ($form->isSubmitted()) {

               //Si le mot de passe n'est pas identique à ce qui est en base, c'est cool
               if (!password_verify($form->get('mdp')->getData(), $participant->getPassword())) {
                   //Si le mot de passe champ 1 est égal de mdp champ 2
                   if ($form->get('mdp')->getData() == $form->get('mdp2')->getData()) {
                       $participant->setPassword(
                           $userPasswordHasher->hashPassword(
                               $participant,
                               $form->get('mdp')->getData()
                           )
                       );
                   } //sinon on lance une erreur
                   else {
                       throw new Exception("Le mot de passe n'est pas équivalent");
                   }
               }
               $participant->setNom($participant->getNom());
               $participant->setPrenom($participant->getPrenom());
               $participant->setTelephone($participant->getTelephone());
               $participant->setPseudo($participant->getPseudo());
               $participant->setPhotoParticipant($participant->getPhotoParticipant());
               $repo->getManager()->persist($participant);
               $repo->getManager()->flush();
               return $this->redirectToRoute('participantdetails', array('id' => $participant->getId()));
           } else {
               return $this->render('participant/edit.html.twig', [
                   'controller_name' => 'ParticipantController',
                   'form' => $form->createView(),
               ]);
           }
       }
       else{
           return throw new Exception("Vous n'êtes pas autorisé à aller sur cette page");

       }

    }
}
