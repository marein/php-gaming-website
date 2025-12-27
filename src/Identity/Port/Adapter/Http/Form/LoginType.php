<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'attr' => [
                    'placeholder' => 'Enter your email address',
                    'autocomplete' => 'email'
                ],
                'help' => 'We\'ll send you a login link to this email address.'
            ])
            ->add('signup', SubmitType::class, [
                'label' => 'Sign in',
                'attr' => ['class' => 'btn-primary w-100'],
                'row_attr' => ['class' => 'form-footer']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['attr' => ['id' => 'login-form']]);
    }
}
