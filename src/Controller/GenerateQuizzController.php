<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenerateQuizzController extends AbstractController
{
    #[Route('/generate/quizz', name: 'app_generate_quizz')]
    public function index(): Response
    {
        return $this->render('generate_quizz/index.html.twig', [
            'controller_name' => 'GenerateQuizzController',
        ]);
    }
}
