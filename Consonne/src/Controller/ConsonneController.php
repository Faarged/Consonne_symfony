<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
//call my entity, Repository and form to use them
use App\Entity\Users;
use App\Entity\Breves;
use App\Entity\Reservation;
use App\Entity\Configuration;
use App\Entity\ResaStat;
use App\Entity\Materiel;
use App\Entity\Game;

use App\Repository\UsersRepository;
use App\Repository\BrevesRepository;
use App\Repository\ReservationRepository;
use App\Repository\ConfigurationRepository;
use App\Repository\ResaStatRepository;
use App\Repository\MaterielRepository;
use App\Repository\GameRepository;

use App\Form\RegistrationType;
use App\Form\ConfigurationType;
use App\Form\BreveType;
use App\Form\ReservationType;
use App\Form\ResaType;
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
    public function home(BrevesRepository $repo, ObjectManager $manager, ReservationRepository $resa, UsersRepository $repuser){
      //stop access if no user connected
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user= $this->getUser();

      //calcul ages and pegis
      $today = new \DateTime();
      $adherents = $repuser->findAll();
      foreach($adherents as $a){
        $naissance = $a->getBirthdate();
        $age_date = $today->diff($naissance);
        $age = $age_date->format('%y');
        //add the pegi
        if ($age < 7) {
          $a->setPegi(3);
        }elseif ($age >= 7 && $age< 9) {
          $a->setPegi(7);
        }elseif ($age >= 9 && $age < 12) {
          $a->setPegi(9);
        }elseif ($age >= 12 && $age < 16) {
          $a->setPegi(12);
        }elseif ($age >= 16 && $age < 18) {
          $a->setPegi(16);
        }else {
          $a->setPegi(18);
        }
        $manager->persist($a);
        $manager->flush();
      }

      //delete reservation 4 months old
      $oldresas = $resa->findAll();
      foreach($oldresas as $old){
        $create = $old->getCreatedAt();
        $res = $today->diff($create);
        $oldres = $res->format('%m');
        if($oldres > 4){
          $manager->remove($old);
          $manager->flush();
        }
      }

      //search last breve created
      $liste = $repo->findLast();

      //for user "adherent": list his reservation of the current day
      $cur_resa = $resa->getByUser($user);

      //for user "admins": list reservations which startTime + last will nearly end
      $admin_resa = $resa->getByDayLimited();
      //dump($admin_resa);

      return $this->render('consonne/home.html.twig', [
          'breve' => $liste,
          'resa' => $cur_resa,
          'resas' => $admin_resa,
      ]);
    }
    /**
    * @Route("/consonne/my_account", name="account")
    */
    public function changePass(GameRepository $repo, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      //find the user
      $user = $this->getUser();
      //Find his pegi to list the game he can play
      $pegi = $user->getPegi($user);
      $liste = $repo->getByPegi($pegi);
      //prepare the form to use
      $form = $this->createForm(UserType::class, $user);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){
        //hash the password
        $hash = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($hash);
        $manager->persist($user);
        $manager->flush();
        //redirection after adding to db
        return $this->redirectToRoute('account');
      }
      return $this->render('consonne/my_account.html.twig', [
        'formUser' => $form->createView(),
        'games' => $liste,
      ]);
    }

    /**
    * @Route("/consonne/{id}/configuration", name="edit_config")
    */
    public function editConfig(Configuration $config, Request $request, ObjectManager $manager){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      //only admin have access to this:
      if($this->getUser()->getIsAdmin()){
        $form = $this->createForm(ConfigurationType::class, $config);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
          $manager->persist($config);
          $manager->flush();
          return $this->redirectToRoute('configuration');
        }
        return $this->render('consonne/edit_config.html.twig', [
          'formConfig' => $form->createView(),
        ]);
      }else {
        return $this->redirectToRoute('home');
      }
    }

    /**
    * @Route("/consonne/{id}/reservations", name="edit_resa")
    */
    public function editResa(Reservation $resa, Request $request, ObjectManager $manager){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user = $this->getUser();
      if($this->getUser()->getIsAdmin()){
        $form = $this->createForm(ResaType::class, $resa);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
          $manager->persist($resa);
          $manager->flush();
          return $this->redirectToRoute('reservation');
        }
        return $this->render('consonne/edit_resa.html.twig', [
          'formResa' => $form->createView(),
        ]);
      }else {
        // If not admin, redirection to the form for connexion
        return $this->redirectToRoute('home');
      }
    }

    /**
    * @Route("/consonne/reservations", name="reservation")
    */
    public function createResa(ResaStat $stats = null, ResaStatRepository $stat, ReservationRepository $repo, Request $request, ObjectManager $manager){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user = $this->getUser();
      if($this->getUser()->getIsAdmin()){
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
          $reservation->setCreatedAt(new \Datetime());
          $reservation->setStartAt(new \Datetime());
          $manager->persist($reservation);
          $manager->flush();
          //ajout table des stats
          $verif = $stat->findOneByDate(new \Datetime());
          if(empty($verif)){
            $stats = new ResaStat();
            $stats->setDate(new \Datetime());
            $stats->setResNumber(1);
            $manager->persist($stats);
            $manager->flush();
          }else {
            $nbres = $verif->getResNumber();
            $verif->setResNumber($nbres+1);
            $manager->persist($verif);
            $manager->flush();
          }
          return $this->redirectToRoute('reservation');
        }

        $liste = $repo->getByDay();

        return $this->render('consonne/reservation.html.twig', [
          'formResa' => $form->createView(),
          'resas' => $liste,
        ]);

      }else{
        $liste = $repo->getByUserLimited($user);

        return $this->render('consonne/reservation.html.twig', [
          'resas' => $liste
        ]);
      }
    }

    /**
    * @Route("/consonne/configuration", name="configuration")
    */
    public function config(ConfigurationRepository $repo){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      if($this->getUser()->getIsAdmin()){

        $liste = $repo->findAll();

        return $this->render('consonne/configuration.html.twig', [
          'configs' => $liste
        ]);
      }else {
        return $this->redirectToRoute('home');
      }
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
       // if isAdmin ==True ok
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
        // if isAdmin = False: redirect to home
        return $this->redirectToRoute('home');
      }
    }
    /**
    *  @Route("/consonne/new_materiel", name="create_materiel")
    */
    public function formMateriel(Request $request, ObjectManager $manager){

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      if($this->getUser()->getIsAdmin()){
       // if isAdmin ==True ok
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
        // if isAdmin = False: redirect to home
        return $this->redirectToRoute('home');
      }
    }
    /**
    *  @Route("/consonne/new_game", name="create_game")
    */
    public function formGame(Request $request, ObjectManager $manager, MaterielRepository $rep){

      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      if($this->getUser()->getIsAdmin()){
       // if isAdmin ==True ok
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
        // if isAdmin = False: redirect to home
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
        if(!$user){
          //create user if don't exist
          $user = new Users();
        }

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
          //verify if users exist
          if(!$user->getId()){
            //add the date
            $user->setSubAt(new \Datetime());
            //calcul the age
            $date = $user->getSubAt($user);
            $naissance = $user->getBirthDate($user);
            $age_date = $date->diff($naissance);
            $age = $age_date->format('%y');
            //add pegi
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
            //vérification of the statut
            $statu = $user->getStatut($user);
            //set isAdmin true or false and add
            if ($statu == 'administrateur') {
              $user->setIsAdmin(TRUE);
            }else{
              $user->setIsAdmin(FALSE);
            }
          }
          //hash the password
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
        $liste = $repo->findByStatut('adherent');

          return $this->render('consonne/list_adherents.html.twig', [
              'adherents' => $liste,
          ]);
       } else {
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
         $liste = $repo->findByStatut('administrateur');

           return $this->render('consonne/list_admins.html.twig', [
               'admins' => $liste,
           ]);
        } else {
          return $this->redirectToRoute('home');
        }

    }
    /**
     * @Route("/consonne/list_resa", name="allresa")
     */
    public function allresa(ReservationRepository $repo)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

       if($this->getUser()->getIsAdmin()){
        $liste = $repo->findAll();

          return $this->render('consonne/list_resa.html.twig', [
              'reservations' => $liste,
          ]);
       } else {
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
         $liste = $repo->findAll();

           return $this->render('consonne/list_breves.html.twig', [
               'breves' => $liste,
           ]);
        } else {
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
         $liste = $repo->findAll();

           return $this->render('consonne/list_materiel.html.twig', [
               'materiels' => $liste,
           ]);
        } else {
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
         $liste = $repo->findAll();

           return $this->render('consonne/list_games.html.twig', [
               'games' => $liste,
           ]);
        } else {
          return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/consonne/statistiques", name="stats")
     */
    public function stats(ReservationRepository $repo, UsersRepository $adher, ResaStatRepository $stats)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

       if($this->getUser()->getIsAdmin()){
         //get all data for the statistic
        $liste = $repo->findAll();
        $pegi3 = $adher->findByPegi(3);
        $pegi7 = $adher->findByPegi(7);
        $pegi9 = $adher->findByPegi(9);
        $pegi12 = $adher->findByPegi(12);
        $pegi16 = $adher->findByPegi(16);
        $pegi18 = $adher->findByPegi(18);
        $allstats = $stats->findAll();

          return $this->render('consonne/stats.html.twig', [
              'reservations' => $liste,
              'pegi3' => $pegi3,
              'pegi7' => $pegi7,
              'pegi9' => $pegi9,
              'pegi12' => $pegi12,
              'pegi16' => $pegi16,
              'pegi18' => $pegi18,
              'stats' => $allstats,
          ]);
       } else {
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
    /**
    * @Route("/consonne/delete/resa/{id}", name="resa_delete")
    *
    */
    public function delete_resa(Reservation $resa, ObjectManager $manager){
      $manager->remove($resa);
      $manager->flush();

      return $this->redirectToRoute('allresa');
    }


}
