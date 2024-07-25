<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SignupType extends AbstractType
{
    /**
     * @param array{confirm: bool} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'attr' => [
                    'placeholder' => 'Enter your email address',
                    'class' => $options['confirm'] ? 'is-valid' : '',
                    'autocomplete' => 'email'
                ],
                'help' => match ($options['confirm']) {
                    false => 'Your email address will be used to log into your account.',
                    true => 'Email verified! It will be used to log into your account.'
                },
                'disabled' => $options['confirm']
            ])
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'Choose your username',
                    'minlength' => 3,
                    'maxlength' => 20,
                    'autocapitalize' => 'off',
                    'autocorrect' => 'off'
                ],
                'help' => match ($options['confirm']) {
                    false => 'Your username will be visible to other players.',
                    true => 'Last chance to change your username! It will be visible to other players.'
                }
            ])
            ->add('signup', SubmitType::class, [
                'label' => $options['confirm'] ? 'Sign me up' : 'Next: Verify Email',
                'attr' => ['class' => 'btn-primary w-100'],
                'row_attr' => ['class' => 'form-footer']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['confirm' => false, 'attr' => ['id' => 'signup-form']]);
    }
}
