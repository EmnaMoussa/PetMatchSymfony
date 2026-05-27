<?php

namespace App\Controller;

use App\Service\SessionUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/conseils', name: 'app_advice')]
    public function advice(SessionUser $sessionUser): Response
    {
        return $this->render('pages/advice.html.twig', [
            'user' => $sessionUser->get(),
        ]);
    }

    #[Route('/confidentialite', name: 'app_privacy')]
    public function privacy(SessionUser $sessionUser): Response
    {
        return $this->render('pages/privacy.html.twig', [
            'user' => $sessionUser->get(),
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, SessionUser $sessionUser): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('contact', (string)$request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Formulaire expire, veuillez recommencer.');
                return $this->redirectToRoute('app_contact');
            }

            $this->addFlash('success', 'Message envoye. Nous vous repondrons bientot.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('pages/contact.html.twig', [
            'user' => $sessionUser->get(),
        ]);
    }
}
