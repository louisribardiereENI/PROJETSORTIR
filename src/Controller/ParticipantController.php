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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participants', name: 'participant_')]

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
    #[Route('/{id}/voir', name: 'details')]
    public function detail(ParticipantRepository $repo,int $id): Response
    {
        $participant = $repo->find($id);
        return $this->render('participant/detail.html.twig', [
            'controller_name' => 'ParticipantController',
            'participant' =>$participant,
        ]);
    }
    #[Route('/{id}/modifier', name: 'edit')]
    public function edit(Request $request,ParticipantRepository $repo,int $id,UserPasswordHasherInterface $userPasswordHasher): ?Response
    {

        $participant = $repo->find($id);

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
               $repo->setPicture($participant,$form);
               $repo->save($participant);
               return $this->redirectToRoute('participant_details', array('id' => $participant->getId()));
           } else {
               return $this->render('participant/edit.html.twig', [
                   'controller_name' => 'ParticipantController',
                   'form' => $form->createView(),
                   'participant' => $participant,
               ]);
           }
       }
       else{
           return throw new Exception("Vous n'êtes pas autorisé à aller sur cette page");

       }

    }

    #[Route('/{id}/administrateur/{admin}', name: 'administrateur')]
    public function administrateur(ParticipantRepository $repo,int $id,bool $admin): Response
    {
        if($this->getUser()) {
            if($this->getUser()->getRoles()[0]=="ADMIN") {
                $participant = $repo->find($id);
                $repo->setAdministration($participant,$admin);
                return $this->redirectToRoute('participant_list');
            }
            else{
                throw new Exception("Vous devez être administrateur pour réaliser cette action !");
            }
        }
        else{
            throw new Exception("Vous devez être authentifié pour réaliser cette action !");
        }
    }

    #[Route('/{id}/activation/{active}', name: 'activation')]
    public function activation(ParticipantRepository $repo,int $id,bool $active): Response
    {
        if($this->getUser()) {
            if($this->getUser()->getRoles()[0]=="ADMIN") {
                $participant = $repo->find($id);
                $participant->setActif($active);
                $repo->save($participant,true);
                return $this->redirectToRoute('participant_list');
            }
            else{
                throw new Exception("Vous devez être administrateur pour réaliser cette action !");
            }
        }
        else{
            throw new Exception("Vous devez être authentifié pour réaliser cette action !");
        }
    }
    #[Route('/{id}/supprimer', name: 'supprimer')]
    public function supprimer(ParticipantRepository $repo,int $id): Response
    {
        if($this->getUser()) {
            if($this->getUser()->getRoles()[0]=="ADMIN") {
                $participant = $repo->find($id);
                $repo->deleteParticipant($participant);
                return $this->redirectToRoute('participant_list');
            }
            else{
                throw new Exception("Vous devez être administrateur pour réaliser cette action !");
            }
        }
        else{
            throw new Exception("Vous devez être authentifié pour réaliser cette action !");
        }
    }
}
