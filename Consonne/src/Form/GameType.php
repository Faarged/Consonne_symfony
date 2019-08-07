<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Materiel;
use App\Repository\MaterielRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('pegi', ChoiceType::class, [
              'placeholder' => 'Pegi du jeu',
              'choices' => [
                '3' => '3',
                '7' => '7',
                '9' => '9',
                '12' => '12',
                '16' =>'16',
                '18' => '18',
              ]
            ])
            ->add('support', EntityType::class, [
                'class'         => Materiel::class,
                'choice_label'  => 'name',
                'placeholder'   => 'Liste des supports disponibles',
                'multiple'      => true,
                'expanded'      => true,
                'mapped'        => false,
                'query_builder' => function(MaterielRepository $rep) {
                    return $rep->getAll();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
