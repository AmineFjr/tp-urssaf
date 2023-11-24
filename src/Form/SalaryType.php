<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SalaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salary', TextType::class, [
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Entrer votre salire net'
                ),
            ])
            ->add('contract', ChoiceType::class, [
                'choices'  => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Apprentissage' => 'apprentissage',
                    'Stage' => 'stage'
                ],
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
