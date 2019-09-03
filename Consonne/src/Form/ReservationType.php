<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Materiel;
use App\Entity\Users;
use App\Entity\Game;
use App\Repository\UsersRepository;
use App\Repository\MaterielRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('createdAt')
            //->add('startAt')
            ->add('duree')
            ->add('materiel', EntityType::class, [
                'class'         => Materiel::class,
                'choice_label'  => 'name',
                'label'         => 'Matériel réservé:',
                'placeholder'   => 'Matériel',
                'query_builder' => function(MaterielRepository $rep) {
                    return $rep->getAll();
                }
              ])
            ->add('game', EntityType::class, [
                'class'         => Game::class,
                'choice_label'  => 'title',
                'label'         => 'Jeu réservé:',
                'placeholder'   => 'Titre du jeu',

              ])
            ->add('user', EntityType::class, [
                'class'         => Users::class,
                'choice_label'  => 'pseudo',
                'label'         => 'Auteur de la réservation:',
                'placeholder'   => 'Pseudo',
                //'expanded'      => true,

              ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
