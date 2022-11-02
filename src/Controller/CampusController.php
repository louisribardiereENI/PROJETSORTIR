<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\ImportCSVType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/campus', name: 'campus_')]
class CampusController extends AbstractController
{

    private $csvParsingOptions = array(
        'finder_in' => 'app/Resources/',
        'finder_name' => 'countries.csv',
        'ignoreFirstLine' => true
    );

    #[Route('/', name: 'list')]
    public function index(Request $request,CampusRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $campus = $repo->findAll();

        $form=$this->createForm(ImportCSVType::class);
        $comp=1;
        foreach ($campus as $camp){
            $form->add('csv'.$comp,FileType::class);
            $comp++;
        }


        $form->handleRequest($request);

        if($form->isSubmitted()&&$form->isValid()){

            for($i=1;$i<=count($campus);$i++){
                $data=$form->get('submitFile')->getData();
                if ($data != "") {
                   $handle=fopen($data->getPathName(), "r");
                   var_dump(fgetcsv($handle));
                   /* $csv = $this->parseCSV();
                    var_dump($csv);*/
                }
            }
        }
        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
            'campuslist' => $campus,
            'form' => $form->createView(),
        ]);
    }

    private function parseCSV(): array
    {
        $ignoreFirstLine = $this->csvParsingOptions['ignoreFirstLine'];

        $finder = new Finder();
        $finder->files()
            ->in($this->csvParsingOptions['finder_in'])
            ->name($this->csvParsingOptions['finder_name'])
        ;
        foreach ($finder as $file) { $csv = $file; }

        $rows = array();
        if (($handle = fopen($csv->getRealPath(), "r")) !== FALSE) {
            $i = 0;
            while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
                $i++;
                if ($ignoreFirstLine && $i == 1) { continue; }
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
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
