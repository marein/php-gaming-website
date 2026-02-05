<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

final class OpenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('size', ChoiceType::class, [
                'data' => '3',
                'label' => 'Size',
                'label_attr' => ['class' => 'btn'],
                'attr' => ['class' => 'btn-group w-100'],
                'choices' => self::sizes(),
                'choice_attr' => static fn() => ['class' => 'btn-check'],
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                    new Choice(choices: self::sizes())
                ]
            ])
            ->add('timer', ChoiceType::class, [
                'data' => 'move:15000',
                'label' => 'Timer',
                'choices' => self::timers(),
                'constraints' => [
                    new NotBlank(),
                    new Choice(choices: self::timers())
                ]
            ])
            ->add('token', ChoiceType::class, [
                'data' => 0,
                'label' => 'Token',
                'label_attr' => ['class' => 'btn'],
                'attr' => ['class' => 'btn-group w-100'],
                'choices' => self::tokens(),
                'choice_attr' => static fn() => ['class' => 'btn-check'],
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                    new Choice(choices: self::tokens())
                ]
            ])
            ->add('open', SubmitType::class, [
                'label' => 'Let\'s play!',
                'attr' => ['class' => 'btn-primary w-100', 'data-open-game-button' => ''],
                'row_attr' => ['class' => 'mb-0']
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function sizes(): array
    {
        return ['3 x 3' => '3', '5 x 5' => '5', '7 x 7' => '7', '9 x 9' => '9'];
    }

    /**
     * @return array<string, string>
     */
    private static function timers(): array
    {
        return ['ttt:move:15000' => 'move:15000', 'ttt:move:30000' => 'move:30000'];
    }

    /**
     * @return array<string, int>
     */
    private static function tokens(): array
    {
        return ['ttt:token_1' => 1, 'ttt:token_2' => 2, 'ttt:token_random' => 0];
    }
}
