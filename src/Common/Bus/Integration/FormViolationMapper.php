<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Gaming\Common\Bus\Violation;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class can be used to map violations, e.g. from an api request, to a Symfony form.
 */
final class FormViolationMapper
{
    public function __construct(
        private readonly ?string $errorMessage = 'Oops! Please review and correct the highlighted fields.',
        private readonly ?TranslatorInterface $translator = null,
        private readonly ?string $translationDomain = null
    ) {
    }

    /**
     * @param Violation[] $violations
     */
    public function mapViolations(FormInterface $form, array $violations): void
    {
        count($violations) > 0 && $this->errorMessage && $form->addError(
            new FormError(
                $this->translator?->trans($this->errorMessage, domain: $this->translationDomain) ?? $this->errorMessage
            )
        );

        foreach ($violations as $violation) {
            $this->mapViolation($form, $violation);
        }
    }

    private function mapViolation(FormInterface $form, Violation $violation): void
    {
        $propertyPaths = explode('.', str_replace(['[', ']'], ['.', ''], $violation->propertyPath()));

        foreach ($propertyPaths as $propertyPath) {
            $form = $form[$propertyPath] ?? $form;
        }

        $parameters = [];
        foreach ($violation->parameters() as $violationParameter) {
            $parameters['{{ ' . $violationParameter->name() . ' }}'] = $violationParameter->value();
        }

        $form->addError(
            new FormError(
                $this->translator?->trans(
                    $violation->identifier(),
                    $parameters,
                    $this->translationDomain
                ) ?? strtr($violation->identifier(), $parameters)
            )
        );
    }
}
