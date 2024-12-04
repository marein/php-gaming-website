<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http\Form;

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
        return ['7 x 6' => '7x6', '8 x 7' => '8x7', '9 x 7' => '9x7', '10 x 7' => '10x7'];
    }

    /**
     * @return array<string, string>
     */
    private static function variants(): array
    {
        return ['Standard' => 'standard', 'PopOut' => 'popout'];
    }

    /**
     * @return array<string, int>
     */
    private static function colors(): array
    {
        return ['Red' => 1, 'Yellow' => 2, 'Random' => -1];
    }
}
