<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/campus', name: 'campus_')]
class CampusController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function index(CampusRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $campus = $repo->findAll();
        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
            'campuslist' => $campus,
        ]);
    }


    #[Route('/creer', name: 'create')]
    public function create(Request $request, ParticipantRepository $repoUser, CampusRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $participant = $repoUser->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        if (!$participant->isAdministrateur()) {
            return $this->redirectToRoute('campus_list');
        }

        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $campus->setNom($form->get('nom')->getData());
            $repo->save($campus, true);
            return $this->redirectToRoute('campus_list');
        } else {
            return $this->render('campus/index.html.twig', [
                'controller_name' => 'CampusController',
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/{id}/modifier', name: 'modifier')]
    public function edit(Request $request, CampusRepository $repo, ParticipantRepository $repoUser, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $campus = $repo->findOneBy(array('id' => $id));
        $participant = $repoUser->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        if (!$participant->isAdministrateur()) {
            return $this->redirectToRoute('campus_list');
        }
        $form = $this->createForm(CampusType::class, $campus);
        $form->get("nom")->setData($campus->getNom());
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $campus->setNom($form->get('nom')->getData());
            $repo->save($campus, true);
            return $this->redirectToRoute('campus_list');
        } else {
            return $this->render('campus/index.html.twig', [
                'controller_name' => 'CampusController',
                'form' => $form->createView(),
                'campus'=>$campus,
            ]);
        }
    }

}
