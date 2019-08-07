<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Users;
use App\Entity\Breves;
use App\Entity\Reservation;
use App\Entity\Materiel;
use App\Entity\Game;

use App\Repository\UsersRepository;
use App\Repository\BrevesRepository;
use App\Repository\ReservationRepository;
use App\Repository\MaterielRepository;
use App\Repository\GameRepository;
use App\Form\RegistrationType;
use App\Form\BreveType;
use App\Form\MaterielType;
use App\Form\GameType;
use App\Form\UserType;


/**
*
*/
class ConsonneController extends AbstractController
{
    /**
     * @Route("/", name="consonne")
     */
    public function index()
    {
        return $this->render('consonne/index.html.twig');
    }

    /**
    *  @Route("/consonne/home", name="home")
    */
    public function home(BrevesRepository $repo, ReservationRepository $resa, UsersRepository $users){

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user= $this->getUser();
      $today = new \DateTime();

      //dernière brèves créée
      $liste = $repo->findLast();

      //pour adhérents: liste des réserv du jour
      $res = $user->getReservations();
      $cur_resa = [];
      $i = 0;
      foreach ($res as $value) {
        $date = $value->getCreatedAt();
        $interval = date_diff($date, $today);

        if ($interval->format('%R%a') > 0) {

        }else{
          $cur_resa[$i++] = $value;
        }
      }
      //pour admins: liste des réserv dont l'heure de début + durée sont plus proche de heure actuelle
      //NE MARCHE PAS
      $endresa = $resa->findAll();
      $reservations = [];
      $i = 0;
      foreach ($endresa as $resByDays) {
        $date = $resByDays->getCreatedAt();
        $start = $resByDays->getStartAt();
        $duree = $resByDays->getDuree();
        $interval = date_diff($date, $today);
        $game_time = date_diff($start, $duree);
        $inter = $start;

        if ($interval->format('%R%a') > 0) {

        }elseif($interval->format('%R%a') <= 0 && $game_time->format('%R%a') <= 0){
          $reservations[$i++] = $resByDays;
        }
      }

      return $this->render('consonne/home.html.twig', [
          'breve' => $liste,
          'resa' => $cur_resa,
          'end' => $reservations,
      ]);
    }
    /**
    * @Route("/consonne/my_account", name="account")
    */
    public function changePass(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user = $this->getUser();
      $form = $this->createForm(UserType::class, $user);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){
        $hash = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($hash);
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('account');
      }
      return $this->render('consonne/my_account.html.twig', [
        'formUser' => $form->createView(),
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
        // il n'est pas admin
        return $this->redirectToRoute('home');
      }
    }
    /**
    *  @Route("/consonne/new_materiel", name="create_materiel")
    */
    public function formMateriel(Request $request, ObjectManager $manager){

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      if($this->getUser()->getIsAdmin()){
       // si c'est true alors il est admin, tu fais ton code
       $materiel = new Materiel();
       $form = $this->createForm(MaterielType::class, $materiel);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()){
         $manager->persist($materiel);
         $manager->flush();
         return $this->redirectToRoute('materiel');
       }
       return $this->render('consonne/create_materiel.html.twig', [
         'formMateriel' => $form->createView(),
       ]);
      } else {
        // il n'est pas admin
        return $this->redirectToRoute('home');
      }
    }
    /**
    *  @Route("/consonne/new_game", name="create_game")
    */
    public function formGame(Request $request, ObjectManager $manager, MaterielRepository $rep){

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      if($this->getUser()->getIsAdmin()){
       // si c'est true alors il est admin, tu fais ton code
       $game = new Game();
       $form = $this->createForm(GameType::class, $game);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()){
         $id_supports = $request->request->get('game')['support'];
         foreach($id_supports as $id_support){
           $support = $rep->find($id_support);
           $game->addSupport($support);
         }
         $manager->persist($game);
         $manager->flush();
         return $this->redirectToRoute('games');
       }
       return $this->render('consonne/create_game.html.twig', [
         'formGame' => $form->createView(),
       ]);
      } else {
        // il n'est pas admin
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
            //vérification du statut
            $statu = $user->getStatut($user);
            //détermination du isAdmin et ajout en bdd
            if ($statu == 'administrateur') {
              $user->setIsAdmin(TRUE);
            }else{
              $user->setIsAdmin(FALSE);
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
     * @Route("/consonne/list_materiel", name="materiel")
     */
    public function materiel_list(MaterielRepository $repo)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($this->getUser()->getIsAdmin()){
         // si c'est true alors il est admin, tu fais ton code
         $liste = $repo->findAll();

           return $this->render('consonne/list_materiel.html.twig', [
               'materiels' => $liste,
           ]);
        } else {
          // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
          return $this->redirectToRoute('home');
        }

    }
    /**
     * @Route("/consonne/list_games", name="games")
     */
    public function game_list(GameRepository $repo)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($this->getUser()->getIsAdmin()){
         // si c'est true alors il est admin, tu fais ton code
         $liste = $repo->findAll();

           return $this->render('consonne/list_games.html.twig', [
               'games' => $liste,
           ]);
        } else {
          // il n'est pas admin donc soit tu le laisses, soit tu le dégages en faisant un return $this->redirectToRoute('ta route')
          return $this->redirectToRoute('home');
        }

    }

    /**
    * @Route("/consonne/delete/materiel/{id}", name="materiel_delete")
    *
    */
    public function delete_materiel(Materiel $materiel, ObjectManager $manager){
      $manager->remove($materiel);
      $manager->flush();

      return $this->redirectToRoute('materiel');
    }
    /**
    * @Route("/consonne/delete/games/{id}", name="game_delete")
    *
    */
    public function delete_game(Game $game, ObjectManager $manager){
      $manager->remove($game);
      $manager->flush();

      return $this->redirectToRoute('games');
    }
    /**
    * @Route("/consonne/delete/user/{id}", name="user_delete")
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
