<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sorties', name: 'sortie')]
class SortieController extends AbstractController
{
    private $serializer;

    #[Route('/', name: 'list')]
    public function index(SortieRepository $repo): Response
    {
        $sorties = $repo->findAll();
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
            'sorties' => $sorties,
        ]);
    }

    #[Route('/creer', name: 'create')]
    public function create(Request $request, SortieRepository $repo, ParticipantRepository $repoUser): Response
    {
        $sortie = new Sortie();
        $form=$this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $user = $repoUser->findOneBy((array)$this->getUser()->getUserIdentifier());
            $sortie->setIdOrganisateur($user);
            $sortie->setIdSiteOrganisateur($user->getIdCampus());
            //$sortie->setPrenom($sortie->getPrenom());
            $repo->getManager()->persist($sortie);
            $repo->getManager()->flush();
            return $this->redirectToRoute('afficher', array('id' => $sortie->getId()));
        }
        else {
            return $this->render('sortie/create.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/{id}/modifier', name: 'modifier')]
    public function modifier(Request $request, SortieRepository $repo, ParticipantRepository $repoUser, int $id): Response
    {
        $sortie = $repo->findOneBy(array('id' => $id));
        $form=$this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $user = $repoUser->findOneBy((array)$this->getUser()->getUserIdentifier());
            $sortie->setIdOrganisateur($user);
            $sortie->setIdSiteOrganisateur($user->getIdCampus());
            //$sortie->setPrenom($sortie->getPrenom());
            $repo->getManager()->persist($sortie);
            $repo->getManager()->flush();
            return $this->redirectToRoute('afficher', array('id' => $sortie->getId()));
        }
        else {
            return $this->render('sortie/modifier.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
            ]);
        }
    }
}
