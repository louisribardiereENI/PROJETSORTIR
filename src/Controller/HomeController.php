<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/', name: 'app_')]
class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',

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

