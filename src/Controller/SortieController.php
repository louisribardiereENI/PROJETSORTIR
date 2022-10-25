<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
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

    #[Route('/create', name: 'create')]
    public function create(Request $request, SortieRepository $repo): Response
    {
        $sortie = new Sortie();
        $form=$this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $sortie->setIdOrganisateur($this->getUser());
            $sortie->setIdSiteOrganisateur($this->getUser()->getIdCampus());
            //$sortie->setPrenom($sortie->getPrenom());
            //$sortie->setTelephone($sortie->getTelephone());
            //$sortie->setPseudo($sortie->getPseudo());
            //$sortie->setPhotoParticipant($sortie->getPhotoParticipant());
            $repo->getManager()->persist($sortie);
            $repo->getManager()->flush();
            return $this->redirectToRoute('sortiedetails', array('id' => $sortie->getId()));
        }
        else {
            return $this->render('sortie/create.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
            ]);
        }
    }
}
