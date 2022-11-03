<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\CampusType;
use App\Form\ImportCSVType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/campus', name: 'campus_')]
class CampusController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function index(Request $request, CampusRepository $repo, UserPasswordHasherInterface $userPasswordHasher, ParticipantRepository $repoUser): Response
    {
        //Redirection
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        //Afficher un campus
        $campus = $repo->findAll();

        $form1 = $this->createForm(ImportCSVType::class);
        $comp = 1;
        foreach ($campus as $camp) {
            $form1->add('csv' . $comp, FileType::class, ['required' => false]);
            $comp++;
        }
        $form1->handleRequest($request);
        $errorGlobal = "Ajout échoué pour les participants : ";
        if ($form1->isSubmitted() && $form1->isValid()) {
            $compteur = 1;

            foreach ($campus as $camp) {
                $data = $form1->get('csv' . $compteur)->getData();
                if ($data != null) {
                    if (($handle = fopen($data->getPathname(), "r")) !== false) {
                        $flag = 1;
                        while (($data = fgetcsv($handle, 0, ";")) !== false) {
                            $error = "";
                            $flag++;
                            if ($flag == 2) {
                                continue;
                            }
                            $participant = new Participant();
                            $participant->setIdCampus($camp);
                            $participant->setEmail($data[0]);
                            if (filter_var($data[5], FILTER_VALIDATE_BOOLEAN)) {
                                $participant->setRoles(array('ADMIN'));
                            } else {
                                $participant->setRoles(array());
                            }
                            $participant->setPassword($userPasswordHasher->hashPassword($participant, $data[1]));
                            $participant->setNom($data[2]);
                            $participant->setPrenom($data[3]);
                            $participant->setTelephone($data[4]);
                            $participant->setAdministrateur(filter_var($data[5], FILTER_VALIDATE_BOOLEAN));
                            $participant->setActif(filter_var($data[6], FILTER_VALIDATE_BOOLEAN));
                            $participant->setPseudo($data[7]);
                            $participant->setPhotoParticipant("/default.jpg");

                            $listParticipants = $repoUser->findAll();
                            foreach ($listParticipants as $unParticipant) {
                                if ($unParticipant->getEmail() == $participant->getEmail()) {
                                    $error .= "\nEmail " . $participant->getEmail() . " déjà existant ! (ligne " . $flag . " du campus " . $compteur . ")";
                                }
                                if ($unParticipant->getPseudo() == $participant->getPseudo()) {
                                    $error .= "\nPseudo " . $participant->getPseudo() . " déjà existant ! (ligne " . $flag . " du campus " . $compteur . ")";
                                }
                            }
                            if ($error == "") {
                                $repoUser->save($participant, true);
                            } else {
                                $errorGlobal .= $error;
                            }

                        }
                        fclose($handle);
                    }
                }
                $compteur++;
            }

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
            'errors'=>$errorGlobal,
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