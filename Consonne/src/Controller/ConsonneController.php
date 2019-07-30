<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Users;
use App\Entity\Role;
use App\Repository\UsersRepository;
use App\Repository\BrevesRepository;
use App\Repository\ReservationRepository;
use App\Form\RegistrationType;

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

      $liste = $repo->findLast();
      $reserv = $resa->findToday();

      return $this->render('consonne/home.html.twig', [
          'breve' => $liste,
          'resa' => $reserv,
      ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}
    /**
    * @Route("/consonne/new_account", name="create_user")
    * @Route("/consonne/{id}/edit", name="user_edit")
    */
    public function formUser(Users $user = null, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){

      $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
    }

    /**
     * @Route("/consonne/list_adherents", name="user_account")
     */
    public function adherents_list(UsersRepository $repo)
    {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $liste = $repo->findByStatut('adherent');

        return $this->render('consonne/list_adherents.html.twig', [
            'adherents' => $liste,
        ]);
    }
    /**
     * @Route("/consonne/list_admins", name="admin_account")
     */
    public function admins_list(UsersRepository $repo)
    {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $liste = $repo->findByStatut('administrateur');

        return $this->render('consonne/list_admins.html.twig', [
            'admins' => $liste,
        ]);
    }

    /**
    * @Route("/consonne/delete/{id}", name="user_delete")
    *
    */
    public function delete(Users $user, ObjectManager $manager){
      $manager->remove($user);
      $manager->flush();

      return $this->redirectToRoute('user_account');
    }


}
