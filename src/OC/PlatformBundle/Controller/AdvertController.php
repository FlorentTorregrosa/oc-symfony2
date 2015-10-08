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

    // On injecte la requête dans les arguments de la méthode
    public function viewAction($id, Request $request)
    {
        // On récupère notre paramètre tag
        $tag = $request->query->get('tag');

        return new Response(
          "Affichage de l'annonce d'id : ".$id.", avec le tag : ".$tag
        );
    }

}
