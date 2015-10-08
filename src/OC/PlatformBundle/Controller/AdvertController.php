<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{
    public function indexAction()
    {
        $content = $this->get('templating')->render('OCPlatformBundle:Advert:index.html.twig');

        return new Response($content);
    }

    public function viewAction($id, Request $request)
    {
        // Récupération de la session
        $session = $request->getSession();

        // On récupère le contenu de la variable user_id
        $userId = $session->get('user_id');

        // On définit une nouvelle valeur pour cette variable user_id
        $session->set('user_id', 91);

        // On n'oublie pas de renvoyer une réponse
        return new Response("Je suis une page de test, je n'ai rien à dire");
    }

}
