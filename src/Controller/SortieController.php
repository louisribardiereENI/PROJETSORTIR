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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
    public function create(Request $request, SortieRepository $repo, ParticipantRepository $repoUser, VilleRepository $repoVille, LieuRepository $repoLieu): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $user = $repoUser->findByEmail($this->getUser()->getUserIdentifier());
            $sortie->setIdOrganisateur($user);
            $sortie->setIdSiteOrganisateur($user->getIdCampus());
            $idEtat = $form->get('idEtat')->getData();
            $sortie->setIdEtat($idEtat);

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
                $repoVille->save($newVille);
                var_dump($newVille);
                $ville = $repoVille->findOneBy(array('nom' => $city, 'codePostal' => $postal));
                var_dump($ville);
            }

            $lieu = $repoLieu->findOneBy(array('nom' => $nomLieu, 'rue' => $address, 'id_ville_id' => $ville->getId()));
            if ($lieu != null) {
                $sortie->setIdLieu($lieu);
            } else {
                $newLieu = new Lieu();
                $newLieu->setNom($nomLieu);
                $newLieu->setRue($nomLieu);
                $newLieu->setLatitude($latitude);
                $newLieu->setLongitude($longitude);
                $newLieu->setIdVille($ville);
                $repoLieu->save($newLieu);
                $lieu = $repoLieu->findOneBy(array('nom' => $city, 'rue' => $postal, 'id_ville_id' => $ville->getId()));
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
            $sortie->setPhotoSortie($photoSortie);

            $repo->save($sortie);
            return $this->redirectToRoute('afficher', array('id' => $sortie->getId()));
        } else {
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
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $user = $repoUser->findOneBy((array)$this->getUser()->getUserIdentifier());
            $sortie->setIdOrganisateur($user);
            $sortie->setIdSiteOrganisateur($user->getIdCampus());
            //$sortie->setPrenom($sortie->getPrenom());
            $repo->getManager()->persist($sortie);
            $repo->getManager()->flush();
            return $this->redirectToRoute('afficher', array('id' => $sortie->getId()));
        } else {
            return $this->render('sortie/modifier.html.twig', [
                'controller_name' => 'SortieController',
                'form' => $form->createView(),
            ]);
        }
    }
}
