<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\HomeType;
use App\Repository\SortieRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

#[Route('/', name: 'app_')]
class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'home')]
    public function index(SortieRepository $repo): Response
    {
    $sorties=$repo->findAll();
    $form=$this->createForm(HomeType::class);

    if($form->isSubmitted()&&$form->isValid()){

    }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form'=>$form->createView(),
            'sorties'=>$sorties,
        ]);
    }


    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/email', name: 'email')]
    public function emailtest(MailerInterface $mailer,): Response
    {
        $email = (new TemplatedEmail())
            ->from(new Address('smtp@baptiste-coutant.fr', 'Sortir'))
            ->to('baptiste.coutant2021@campus-eni.fr')
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/test.html.twig');

        $mailer->send($email);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}

