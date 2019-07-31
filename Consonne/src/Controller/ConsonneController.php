<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Users;
use App\Entity\Breves;

use App\Repository\UsersRepository;
use App\Repository\BrevesRepository;
use App\Repository\ReservationRepository;
use App\Form\RegistrationType;
use App\Form\BreveType;

class ConsonneController extends AbstractController
{
    /**
     * @Route("/consonne", name="consonne")
     */
    public function index()
    {
        return $this->render('consonne/index.html.twig', [
            'controller_name' => 'ConsonneController',
        ]);
    }

    /**
    *  @Route("/consonne/home", name="home")
    */
    public function home(BrevesRepository $repo, ReservationRepository $resa, UsersRepository $users){

       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user= $this->getUser()->getId();
      //dernière brèves créée
      $liste = $repo->findLast();
      //pour adhérents: liste des réserv du jour
      $reserv = $resa->findToday($user);
      //pour admins: liste des réserv dont l'heure de début + durée sont plus proche de heure actuelle
      $endresa = $resa->endresa();

      return $this->render('consonne/home.html.twig', [
          'breve' => $liste,
          'resa' => $reserv,
          'end' => $endresa,
      ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}

    /**
    *  @Route("/consonne/new_breves", name="new_breves")
    */
    public function formBreves(Request $request, ObjectManager $manager){

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      if($this->getUser()->getIsAdmin()){
       // si c'est true alors il est admin, tu fais ton code
       $breve = new Breves();
       $form = $this->createForm(BreveType::class, $breve);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()){
         $manager->persist($breve);
         $manager->flush();
         return $this->redirectToRoute('breves');
       }
       return $this->render('consonne/create_breve.html.twig', [
         'formBreve' => $form->createView(),
       ]);
      } else {
        // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
        return $this->redirectToRoute('home');
      }
    }
    /**
    * @Route("/consonne/new_account", name="create_user")
    * @Route("/consonne/{id}/edit", name="user_edit")
    */
    public function formUser(Users $user = null, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){

       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

       if($this->getUser()->getIsAdmin()){
        // si c'est true alors il est admin, tu fais ton code
        if(!$user){
          $user = new Users();
        }

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
          //Vérifie si compte à créer ou à éditer
          if(!$user->getId()){
            //Date de création du compte
            $user->setSubAt(new \Datetime());
            //calcul de l'age
            $date = $user->getSubAt($user);
            $naissance = $user->getBirthDate($user);
            $age_date = $date->diff($naissance);
            $age = $age_date->format('%y');
            //ajout du pegi
            if ($age < 7) {
              $user->setPegi(3);
            }elseif ($age >= 7 && $age< 9) {
              $user->setPegi(7);
            }elseif ($age >= 9 && $age < 12) {
              $user->setPegi(9);
            }elseif ($age >= 12 && $age < 16) {
              $user->setPegi(12);
            }elseif ($age >= 16 && $age < 18) {
              $user->setPegi(16);
            }else {
              $user->setPegi(18);
            }
          }

          $hash = $encoder->encodePassword($user, $user->getPassword());
          $user->setPassword($hash);
          $manager->persist($user);
          $manager->flush();

          return $this->redirectToRoute('user_account');
        }

        return $this->render('consonne/create_account.html.twig', [
          'formUser' => $form->createView(),
          'editMode' => $user->getId() !== null
        ]);
       } else {
         // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
         return $this->redirectToRoute('home');
       }


    }

    /**
     * @Route("/consonne/list_adherents", name="user_account")
     */
    public function adherents_list(UsersRepository $repo)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

       if($this->getUser()->getIsAdmin()){
        // si c'est true alors il est admin, tu fais ton code
        $liste = $repo->findByStatut('adherent');

          return $this->render('consonne/list_adherents.html.twig', [
              'adherents' => $liste,
          ]);
       } else {
         // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
         return $this->redirectToRoute('home');
       }

    }
    /**
     * @Route("/consonne/list_admins", name="admin_account")
     */
    public function admins_list(UsersRepository $repo)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($this->getUser()->getIsAdmin()){
         // si c'est true alors il est admin, tu fais ton code
         $liste = $repo->findByStatut('administrateur');

           return $this->render('consonne/list_admins.html.twig', [
               'admins' => $liste,
           ]);
        } else {
          // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
          return $this->redirectToRoute('home');
        }

    }
    /**
     * @Route("/consonne/list_breves", name="breves")
     */
    public function breves_list(BrevesRepository $repo)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($this->getUser()->getIsAdmin()){
         // si c'est true alors il est admin, tu fais ton code
         $liste = $repo->findAll();

           return $this->render('consonne/list_breves.html.twig', [
               'breves' => $liste,
           ]);
        } else {
          // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
          return $this->redirectToRoute('home');
        }

    }

    /**
    * @Route("/consonne/delete/{id}", name="user_delete")
    *
    */
    public function delete_user(Users $user, ObjectManager $manager){
      $manager->remove($user);
      $manager->flush();

      return $this->redirectToRoute('user_account');
    }
    /**
    * @Route("/consonne/delete/breve/{id}", name="breve_delete")
    *
    */
    public function delete_breve(Breves $breve, ObjectManager $manager){
      $manager->remove($breve);
      $manager->flush();

      return $this->redirectToRoute('breves');
    }


}
