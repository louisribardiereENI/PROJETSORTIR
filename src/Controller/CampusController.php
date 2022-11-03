<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\ImportCSVType;
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
    public function index(Request $request, CampusRepository $repo, ParticipantRepository $repoUser): Response
    {
        //Redirection
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        //Afficher un campus
        $campus = $repo->findAll();
        $form1 = $this->createForm(ImportCSVType::class);
        $form1->handleRequest($request);
        if ($form1->isSubmitted() && $form1->isValid()) {

            for ($i = 1; $i <= count($campus); $i++) {
                $data = $request->get('csv' . $i);
            }
        }
        $participant = $repoUser->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        if (!$participant->isAdministrateur()) {
            return $this->redirectToRoute('campus_list');
        }

        //Créer un campus
        $campus2 = new Campus();
        $form2 = $this->createForm(CampusType::class, $campus2);
        $form2->handleRequest($request);
        if ($form2->isSubmitted()) {
            $campus2->setNom($form2->get('nom')->getData());
            $repo->save($campus2, true);
            return $this->redirectToRoute('campus_list');
        }

        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
            'campuslist' => $campus,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit')]
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
            return $this->render('campus/edit.html.twig', [
                'controller_name' => 'CampusController',
                'form3' => $form->createView(),
                'campus' => $campus,
            ]);
        }
    }

    #[Route('/{id}/supprimer', name: 'delete')]
    public function delete(Request $request, CampusRepository $repo, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $campus3 = $repo->findOneBy(array('id' => $id));
        $repo->deleteCampus($campus3);
        return $this->redirectToRoute('campus_list');

    }

}


