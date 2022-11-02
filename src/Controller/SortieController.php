<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

#[Route('/sorties', name: 'sortie_')]
class SortieController extends AbstractController
{
    private $serializer;

    #[Route('/', name: 'list')]
    public function index(SortieRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $sorties = $repo->findAll();
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
            'sorties' => $sorties,
        ]);
    }

    #[Route('/creer', name: 'create')]
    public function create(Request $request, SortieRepository $repo, ParticipantRepository $repoUser, VilleRepository $repoVille, LieuRepository $repoLieu, EtatRepository $repoEtat): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $sortie = $this->submit($request, $repo, $repoUser, $repoVille, $repoLieu, $repoEtat, $sortie, $form, false);
            $repo->save($sortie, true);
            return $this->redirectToRoute('sortie_details', array('id' => $sortie->getId()));
        } else {
            return $this->render('sortie/create.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
            ]);
        }
    }

    private function submit(Request $request, SortieRepository $repo, ParticipantRepository $repoUser, VilleRepository $repoVille, LieuRepository $repoLieu, EtatRepository $repoEtat, Sortie $sortie, Form $form, bool $isEdit): Sortie {

        $user = $repoUser->findByEmail($this->getUser()->getUserIdentifier());
        $sortie->setIdOrganisateur($user);
        $sortie->setIdSiteOrganisateur($user->getIdCampus());

        if ( !$isEdit) {
            $idEtat = $repoEtat->findOneBy(array('libelle' => 'Ouverte'));
            $sortie->setIdEtat($idEtat);
        }

        $address = $request->request->get('address');
        $city = $request->request->get('city');
        $postal = $request->request->get('postal');

        $latitude = $request->request->get('lat');
        $longitude = $request->request->get('lng');

        $nomLieu = $form->get('nomLieu')->getData();
        $ville = $repoVille->findOneBy(array('nom' => $city, 'codePostal' => $postal));
        if ($ville == null) {
            $newVille = new Ville();
            $newVille->setNom($city);
            $newVille->setCodePostal($postal);
            $repoVille->save($newVille, true);
            $ville = $repoVille->findOneBy(array('nom' => $city, 'codePostal' => $postal));
        }

        $lieu = $repoLieu->findOneBy(array('nom' => $nomLieu, 'rue' => $address, 'idVille' => $ville->getId()));
        if ($lieu != null) {
            $sortie->setIdLieu($lieu);
        } else {
            $newLieu = new Lieu();
            $newLieu->setNom($nomLieu);
            $newLieu->setRue($address);
            $newLieu->setLatitude((float) $latitude);
            $newLieu->setLongitude((float) $longitude);
            $newLieu->setIdVille($ville);
            $repoLieu->save($newLieu, true);
            $lieu = $repoLieu->findOneBy(array('nom' => $nomLieu, 'rue' => $address, 'idVille' => $ville->getId()));
            $sortie->setIdLieu($lieu);
            $sortie->getIdLieu()->setIdVille($ville);
        }

        $nom = $form->get('nom')->getData();
        $dateHeureDebut = $form->get('dateHeureDebut')->getData();
        $duree = $form->get('duree')->getData();
        $dateLimiteInscription = $form->get('dateLimiteInscription')->getData();
        $nbrInscriptionMax = $form->get('nbInscriptionsMax')->getData();
        $infoSortie = $form->get('infosSortie')->getData();
        $photoSortie = $form->get('photoSortie')->getData();

        $sortie->setNom($nom);
        $sortie->setDateHeureDebut($dateHeureDebut);
        $sortie->setDuree($duree);
        $sortie->setDateLimiteInscription($dateLimiteInscription);
        $sortie->setNbInscriptionsMax($nbrInscriptionMax);
        $sortie->setInfosSortie($infoSortie);
        if ( !$isEdit) {
            $sortie->setPhotoSortie('default.jpg');
        }
        $repo->save($sortie, true);
        if($photoSortie) {
            $photoSortie->move('img/sorties', $sortie->getId() . '.jpg');
            $sortie->setPhotoSortie($sortie->getId().'.jpg');
        }
        $repo->save($sortie, true);
        return $sortie;
    }

    #[Route('/{id}/modifier', name: 'modifier')]
    public function modifier(Request $request, SortieRepository $repo, ParticipantRepository $repoUser, VilleRepository $repoVille, LieuRepository $repoLieu, EtatRepository $repoEtat, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $sortie = $repo->findOneBy(array('id' => $id));
        $participant = $repoUser->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        if ($participant->getId() != $sortie->getIdOrganisateur()->getId() && !$participant->isAdministrateur()) {
            return $this->redirectToRoute('sortie_details', array('id' => $sortie->getId()));
        }
        $form = $this->createForm(SortieType::class, $sortie);
        $form->get("nomLieu")->setData($sortie->getIdLieu()->getNom());
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $sortie = $this->submit($request, $repo, $repoUser, $repoVille, $repoLieu, $repoEtat, $sortie, $form, true);
            $repo->save($sortie, true);
            return $this->redirectToRoute('sortie_details', array('id' => $sortie->getId()));
        } else {
            return $this->render('sortie/modifier.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
                'sortie'=>$sortie,
            ]);
        }
    }

    #[Route('/{id}/voir', name: 'details')]
    public function detail(SortieRepository $repo,int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $sortie = $repo->find($id);
        return $this->render('sortie/afficher.html.twig', [
            'controller_name' => 'SortieController',
            'sortie' =>$sortie,
        ]);
    }

    #[Route('/{id}/inscription', name: 'inscription')]
    public function inscription(SortieRepository $repo, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $sortie = $repo->findOneBy(array('id' => $id));
        if ($sortie->getIdEtat()->getLibelle() == "Ouverte") {
            $participant = $sortie->getIdParticipant();
            if (!$participant->contains($this->getUser())) {
                $participantList = $sortie->getIdParticipant();
                if ($participantList->count() < $sortie->getNbInscriptionsMax() && $sortie->getDateLimiteInscription() > new DateTime("now")) {
                    $sortie->addIdParticipant($this->getUser());
                }
            } else {
                $sortie->removeIdParticipant($this->getUser());
            }
        $repo->save($sortie, true);
        }
        return $this->redirectToRoute('sortie_details', array('id' => $sortie->getId()));
    }

    #[Route('/{id}/annuler', name: 'annuler')]
    public function annuler(SortieRepository $repo, ParticipantRepository $repoUser, EtatRepository $repoEtat, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $sortie = $repo->findOneBy(array('id' => $id));
        $participant = $repoUser->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        if ($participant->getId() != $sortie->getIdOrganisateur()->getId() && !$participant->isAdministrateur()) {
            return $this->redirectToRoute('sortie_details', array('id' => $sortie->getId()));
        }
        if ($sortie->getIdEtat()->getLibelle() == "Annulée") {
            $etat = $repoEtat->findOneBy(array('libelle' => 'Ouverte'));
            $sortie->setIdEtat($etat);
        } else {
            $participantList = $sortie->getIdParticipant();
            foreach ($participantList as $participantALaSortie) {
                $sortie->removeIdParticipant($participantALaSortie);
            }
            $etat = $repoEtat->findOneBy(array('libelle' => 'Annulée'));
            $sortie->setIdEtat($etat);
        }
        $repo->save($sortie, true);
        return $this->redirectToRoute('sortie_details', array('id' => $sortie->getId()));
    }
}
