<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function edit(Request $request,ManagerRegistry $repo,int $id): Response
    {
        $participant = $repo->getManager()->find(Participant::class,$id);
        $form=$this->createForm(ParticipantType::class,$participant);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $participant->setNom($participant->getNom());
            $participant->setPrenom($participant->getPrenom());
            $participant->setTelephone($participant->getTelephone());
            $participant->setPseudo($participant->getPseudo());
            $participant->setPhotoParticipant($participant->getPhotoParticipant());
            $repo->getManager()->persist($participant);
            $repo->getManager()->flush();
            return $this->redirectToRoute('participantdetails', array('id' => $participant->getId()));
        }
        else {
            return $this->render('participant/edit.html.twig', [
                'controller_name' => 'ParticipantController',
                'form' => $form->createView(),
            ]);
        }
    }
}
