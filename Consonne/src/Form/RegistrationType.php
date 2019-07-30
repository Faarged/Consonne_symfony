<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('firstname')
            ->add('birthDate', BirthdayType::class)
            ->add('pseudo')
            ->add('cardNum')
            ->add('statut', ChoiceType::class, [
              'placeholder' => 'Choix du statut',
              'choices' => [
                'Adhérent' => 'adherent',
                'Administrateur' => 'administrateur'
              ]
            ])
            ->add('endSubAt')
            ->add('gameTime')
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            ->add('userRoles', EntityType::class, [
              'class' => Role::class,
              'choice_label' => 'label',
              'required' => false,
              'placeholder' => 'Choisir le role',
              'label' => 'Role utilisateur'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
