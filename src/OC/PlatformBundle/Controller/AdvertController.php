<?php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Bigbrother\BigbrotherEvents;
use OC\PlatformBundle\Bigbrother\MessagePostEvent;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\AdvertType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page < 1) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // Ici je fixe le nombre d'annonces par page à 3
        // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 3;

        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
          ->getManager()
          ->getRepository('OCPlatformBundle:Advert')
          ->getAdverts($page, $nbPerPage)
        ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts)/$nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
          'listAdverts' => $listAdverts,
          'nbPages'     => $nbPages,
          'page'        => $page
        ));
    }

    public function viewAction($id)
    {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Pour récupérer une annonce unique : on utilise find()
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        // On vérifie que l'annonce avec cet id existe bien
        if ($advert === null) {
            throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On récupère la liste des advertSkill pour l'annonce $advert
        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findByAdvert($advert);

        // Puis modifiez la ligne du render comme ceci, pour prendre en compte les variables :
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
          'advert'           => $advert,
          'listAdvertSkills' => $listAdvertSkills,
        ));
    }

    /**
     * @Security("has_role('ROLE_AUTEUR')")
     */
    public function addAction(Request $request)
    {
        $advert = new Advert();
        $form = $this->get('form.factory')->create(new AdvertType(), $advert);

        if ($form->handleRequest($request)->isValid()) {
            // On crée l'évènement avec ses 2 arguments
            $event = new MessagePostEvent($advert->getContent(), $advert->getUser());

            // On déclenche l'évènement
            $this
              ->get('event_dispatcher')
              ->dispatch(BigbrotherEvents::onMessagePost, $event)
            ;

            // On récupère ce qui a été modifié par le ou les listeners, ici le message
            $advert->setContent($event->getMessage());

            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // On récupère l'entité correspondant à l'id $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        // Si l'annonce n'existe pas, on affiche une erreur 404
        if ($advert == null) {
            throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create(new AdvertEditType(), $advert);

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
          'advert' => $advert,
          'form' => $form->createView(),
        ));
    }

    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirect($this->generateUrl('oc_platform_home'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
          'advert' => $advert,
          'form'   => $form->createView()
        ));
    }

    public function menuAction($limit = 3)
    {
        $listAdverts = $this->getDoctrine()
          ->getManager()
          ->getRepository('OCPlatformBundle:Advert')
          ->findBy(
            array(),                 // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit,                  // On sélectionne $limit annonces
            0                        // À partir du premier
          );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
          'listAdverts' => $listAdverts
        ));
    }

}
