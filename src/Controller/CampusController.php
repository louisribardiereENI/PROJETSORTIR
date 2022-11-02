<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
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
            'campus' => $campus,
        ]);
    }


    #[Route('/creer', name: 'create')]
    public function create(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            return $this->redirectToRoute('campus_list');
        } else {
            return $this->render('sortie/create.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
            ]);
        }
    }
}