<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Form\RegistrationType;

class ConsonneController extends AbstractController
{
    /**
     * @Route("/", name="consonne")
     */
    public function index()
    {
        return $this->render('consonne/index.html.twig', [
            'controller_name' => 'ConsonneController',
        ]);
    }
    /**
    * @Route("/consonne/new_account", name="create_user")
    * @Route("/consonne/{id}/edit", name="user_edit")
    */
    public function formUser(Users $user = null, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){

      if(!$user){
        $user = new Users();
      }


      $form = $this->createForm(RegistrationType::class, $user);

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){
        if(!$user->getId()){
          $user->setSubAt(new \Datetime());
          $date = $user->getSubAt($user);
          $naissance = $user->getBirthDate($user);
          $age = $date->diff($naissance);
          if ($age <= 7) {
            $user->setPegi(7);
          }elseif ($age > 7 and $age<= 9) {
            $user->setPegi(9);
          }elseif ($age > 9 and $age <= 12) {
            $user->setPegi(12);
          }elseif ($age > 12 and $age <= 16) {
            $user->setPegi(16);
          }else {
            $user->setPegi(18);
          }
        }

        $hash = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($hash);
        $manager->persist($user);
        $manager->flush();

      //  return $this->redirectToRoute('my_account', ['id' => $user->getId()]);
      }

      return $this->render('consonne/create_account.html.twig', [
        'formUser' => $form->createView(),
        'editMode' => $user->getId() !== null
      ]);
    }
}
