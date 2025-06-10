<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Http\Form;

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
                'data' => '7x6',
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
            ->add('variant', ChoiceType::class, [
                'data' => 'standard',
                'label' => 'Variant',
                'choices' => self::variants(),
                'choice_attr' => static fn(string $value): array => $value === 'popout'
                    ? ['disabled' => 'disabled']
                    : [],
                'constraints' => [
                    new NotBlank(),
                    new Choice(choices: self::variants())
                ]
            ])
            ->add('timer', ChoiceType::class, [
                'data' => 'move:60',
                'label' => 'Time control',
                'choices' => self::timeControls(),
                'constraints' => [
                    new NotBlank(),
                    new Choice(choices: self::timeControls())
                ]
            ])
            ->add('color', ChoiceType::class, [
                'data' => -1,
                'label' => 'Color',
                'label_attr' => ['class' => 'btn'],
                'attr' => ['class' => 'btn-group w-100'],
                'choices' => self::colors(),
                'choice_attr' => static fn() => ['class' => 'btn-check'],
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                    new Choice(choices: self::colors())
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
        return ['7 x 6' => '7x6', '9 x 6' => '9x6', '11 x 8' => '11x8', '13 x 10' => '13x10'];
    }

    /**
     * @return array<string, string>
     */
    private static function variants(): array
    {
        return ['Standard' => 'standard', 'PopOut' => 'popout'];
    }

    /**
     * @return array<string, string>
     */
    private static function timeControls(): array
    {
        return ['1m/move' => 'move:5', '5+0 (5m + 0s/move)' => 'game:300:0', '5+3 (5m + 3s/move)' => 'game:300:3'];
    }

    /**
     * @return array<string, int>
     */
    private static function colors(): array
    {
        return ['Red' => 1, 'Yellow' => 2, 'Random' => -1];
    }
}
