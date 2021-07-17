<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\NextTrip;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class MeteoController extends AbstractController
{
        /**
     * @Route("/", name="home")
     */


    public function create(Request $request): Response
    {
        $nextTrip = new NextTrip;
        $form = $this->createFormBuilder($nextTrip)
        ->add('ville1')
        ->add('ville2')
        ->add('apply', SubmitType::class, [
            'label' => 'Comparer !'
        ])
        ->getForm();

        $form->handleRequest($request);

        dump($nextTrip);

        return $this->render('meteo/home.html.twig', [
            'formVilles' => $form->createView()
        ]);
    }


        /**
     * @Route("/meteo", name="meteo")
     */
    public function index(): Response
    {
        return $this->render('meteo/index.html.twig', [
            'controller_name' => 'MeteoController',
        ]);
    }
}
