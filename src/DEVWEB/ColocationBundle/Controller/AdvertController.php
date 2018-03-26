<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace DEVWEB\ColocationBundle\Controller;

use DEVWEB\ColocationBundle\Entity\Advert;
use DEVWEB\ColocationBundle\Form\AdvertType;
use DEVWEB\ColocationBundle\Form\AdvertEditType;
use DEVWEB\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use MarcW\RssWriter\Bridge\Symfony\HttpFoundation\RssStreamedResponse;
use MarcW\RssWriter\Extension\Atom\AtomLink;
use MarcW\RssWriter\Extension\Atom\AtomWriter;
use MarcW\RssWriter\Extension\Core\Channel;
use MarcW\RssWriter\Extension\Core\Cloud;
use MarcW\RssWriter\Extension\Core\CoreWriter;
use MarcW\RssWriter\Extension\Core\Enclosure;
use MarcW\RssWriter\Extension\Core\Guid;
use MarcW\RssWriter\Extension\Core\Image;
use MarcW\RssWriter\Extension\Core\Item;
use MarcW\RssWriter\Extension\Core\Source;
use MarcW\RssWriter\Extension\DublinCore\DublinCore;
use MarcW\RssWriter\Extension\DublinCore\DublinCoreWriter;
use MarcW\RssWriter\Extension\Sy\Sy;
use MarcW\RssWriter\Extension\Sy\SyWriter;
use MarcW\RssWriter\RssWriter;


class AdvertController extends Controller
{
  // controller de la vue menu.html.twig
  public function menuAction($limit)
  {
    $em = $this->getDoctrine()->getManager();

    $listAdverts = $em->getRepository('DEVWEBColocationBundle:Advert')->findBy(
      array(),
      array('date' => 'desc'),
      $limit,
      0
    );

  return $this->render('@DEVWEBColocation/Advert/menu.html.twig', array(
    'listAdverts' => $listAdverts
  ));
  }
  // controller de la vue index.html.twig
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    $listAdverts = $this->getDoctrine()
      ->getManager()
      ->getRepository('DEVWEBColocationBundle:Advert')
      ->findAll()
    ;

    return $this->render('@DEVWEBColocation/Advert/index.html.twig', array(
      'listAdverts' => $listAdverts,
    ));
  }
// controller de la vue view.html.twig
  public function viewAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    $affichage = false;
    $postule = false;

    $advert = $em->getRepository('DEVWEBColocationBundle:Advert')->find($id);
    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }
    else{
      $securityContext = $this->container->get('security.authorization_checker');
      if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        $usr = $this->getUser()->getUsername();
        if($usr == "admin"){
          $affichage = true;
          $postule = true;
        }
        else if ($advert->getAuthor() == $usr){
          $affichage = true;
        }
        else {
          $postule = true;
        }
      }
    }
  if ($advert->getNbPlaces() == 0){
    $postule = false;
  }
  return $this->render('@DEVWEBColocation/Advert/view.html.twig', array(
    'advert'           => $advert,
    'affichage'        => $affichage,
    'postule'          => $postule,
  ));
  }
// controller de la vue add.html.twig
  public function addAction(Request $request)
  {
    $advert = new Advert();
    $form   = $this->get('form.factory')->create(AdvertType::class, $advert);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $usr = $this->getUser();
      $usrname = $usr->getUsername();
      $advert->setAuthor($usrname);
      $em = $this->getDoctrine()->getManager();
      $em->persist($advert);
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      return $this->redirectToRoute('devweb_colocation_view', array('id' => $advert->getId()));
    }

    return $this->render('@DEVWEBColocation/Advert/add.html.twig', array(
      'form' => $form->createView(),
    ));
  }
  // controller de la vue edit.html.twig
  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $advert = $em->getRepository('DEVWEBColocationBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('devweb_colocation_view', array('id' => $advert->getId()));
    }

    return $this->render('@DEVWEBColocation/Advert/edit.html.twig', array(
      'advert' => $advert,
      'form'   => $form->createView(),
    ));
  }
  // controller de la vue delete.html.twig
  public function deleteAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $advert = $em->getRepository('DEVWEBColocationBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($advert);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

      return $this->redirectToRoute('devweb_colocation_home');
    }

    return $this->render('@DEVWEBColocation/Advert/delete.html.twig', array(
      'advert' => $advert,
      'form'   => $form->createView(),
    ));
  }
  // controller de la vue postuler.html.twig
  public function postulerAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $advert = $em->getRepository('DEVWEBColocationBundle:Advert')->find($id);
    $usr = $this->getUser();
    $nbPlace = $advert->getNbPlaces()-1;
    $advert->setNbPlaces($nbPlace);
    $advert->addUser($usr);
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($advert);
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Vous avez postulé à cette annonce.');

      return $this->redirectToRoute('devweb_colocation_home');
    }

    return $this->render('@DEVWEBColocation/Advert/postuler.html.twig', array(
      'advert' => $advert,
      'form'   => $form->createView()
    ));
  }
  // controller de la vue search.html.twig
  public function searchAction(Request $request)
  {
    $listAdverts = null;
    $form = $this->createFormBuilder(null)
    ->add('search',          TextType::class)
    ->add('parametre',            ChoiceType::class, array('choices'  => array('Titre' => 1,
                                                                          'Type' => 2,
                                                                          'Auteur' => 3
                                                                          )))
    ->add('Rechercher',      SubmitType::class)
    ->getForm();

    $repository = $this
      ->getDoctrine()
      ->getManager()
      ->getRepository('DEVWEBColocationBundle:Advert')
    ;

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $donnees = $form->getData();
      if($donnees['parametre'] == '1'){
        $listAdverts = $repository->findBy(array('title' => $donnees['search']));
        foreach ($listAdverts as $advert) {
        }
      }
      else if($donnees['parametre'] == '2'){
        $listAdverts = $repository->findBy(array('type' => $donnees['search']));
        foreach ($listAdverts as $advert) {
        }
      }
      else if($donnees['parametre'] == '3'){
        $listAdverts = $repository->findBy(array('author' => $donnees['search']));
        foreach ($listAdverts as $advert) {
        }
      }
    }
    return $this->render('@DEVWEBColocation/Advert/search.html.twig', array(
      'listAdverts' => $listAdverts,
      'form' => $form->createView()
    ));
  }
  // controller de la vue param.html.twig
  public function paramAction(Request $request)
  {
    $form = $this->createFormBuilder(null)
    ->add('parametre',            ChoiceType::class, array('choices'  => array('Sombre' => 'sombre',
                                                                          'Clair' => 'clair'
                                                                          )))
    ->add('Changer',      SubmitType::class)
    ->getForm();
    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $donnees = $form->getData();
      $activeTheme = $this->container->get('liip_theme.active_theme');
      $activeTheme->setName($donnees['parametre']);
    }
    return $this->render('@DEVWEBColocation/Advert/param.html.twig', array(
      'form' => $form->createView()
    ));
  }
  // controller de la vue feed.xml.twig
  public function feedAction(Request $request)
  {
    $channel = new Channel();
    $rssWriter = new RssWriter(null, [], true);
     $rssWriter->registerWriter(new CoreWriter());
     $rssWriter->registerWriter(new AtomWriter());

     $channel->setTitle('Annonces')
             ->setDescription('Liste des annonces du site')
             ->setLanguage('fr')
     ;
     $listAdverts = $this->getDoctrine()
       ->getManager()
       ->getRepository('DEVWEBColocationBundle:Advert')
       ->findAll()
     ;
     foreach ($listAdverts as $advert) {
       $item = new Item();
       $item->setTitle($advert->getTitle())
           ->setDescription($advert->getContent())
           ->setAuthor($advert->getAuthor())
           ->setPubDate($advert->getDate())
           ->setPlaces($advert->getNbPlaces());
       ;
       $channel->addItem($item);
     }

     $xml = $rssWriter->writeChannel($channel);
    return new RssStreamedResponse($channel, $this->get('marcw_rss_writer.rss_writer'));

  }
}
