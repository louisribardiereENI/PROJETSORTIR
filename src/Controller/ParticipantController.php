<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participants', name: 'app_participant')]

class ParticipantController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function index(ParticipantRepository $repo): Response
    {
        $participants = $repo->findAll();
        if(count($participants)>0) {
            return $this->render('participant/index.html.twig', [
                'controller_name' => 'ParticipantController',
                'participants' => $participants,
            ]);
        }
        else{
            return new \Exception("La base de donnée ne contient pas de participants");
        }
    }
    #[Route('/{id}', name: 'details')]
    public function detail(ParticipantRepository $repo,int $id): Response
    {
        $participant = $repo->find($id);
        return $this->render('participant/detail.html.twig', [
            'controller_name' => 'ParticipantController',
            'participant' =>$participant,
        ]);
    }
}
