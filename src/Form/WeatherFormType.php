<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class WeatherFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                                     'message' => 'City field cannot be empty.',
                                 ]),
                    new Length([
                                   'min' => 3,
                                   'max' => 50,
                                   'minMessage' => 'City name must be at least {{ limit }} characters long.',
                                   'maxMessage' => 'City nam cannot be longer than {{ limit }} characters.',
                               ]),
                ],
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 50,
                    'placeholder' => 'City',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Show current weather',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'data-turbo' => 'false',
            ],
        ]);
    }
}
