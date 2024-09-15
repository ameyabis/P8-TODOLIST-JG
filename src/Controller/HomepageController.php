<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController
{
    #[Route(path: '/', name: 'homepage', methods: ['GET'])]
    public function showHomepage(): Response
    {
        return $this->render('homepage/index.html.twig');
    }
}
