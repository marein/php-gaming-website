<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus\Integration;

use Gaming\Common\Bus\Integration\FormViolationMapper;
use Gaming\Common\Bus\Violation;
use Gaming\Common\Bus\ViolationParameter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

final class FormViolationMapperTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldHandleEmptyViolations(): void
    {
        $form = (new FormFactoryBuilder(true))->getFormFactory()->createBuilder()
            ->add('email', TextType::class)
            ->add('username', TextType::class)
            ->getForm();

        (new FormViolationMapper())->mapViolations($form, []);

        self::assertCount(0, $form->getErrors(true, true));
    }

    /**
     * @test
     */
    public function itShouldMapToTheCorrectFields(): void
    {
        $form = $this->createFormAndMapViolations(new FormViolationMapper());

        self::assertCount(2, $errors = $form->getErrors());
        self::assertSame(
            'Oops! Please review and correct the highlighted fields.',
            $errors[0]->getMessage()
        );
        self::assertSame('invalid nofield test', $errors[1]->getMessage());

        self::assertCount(1, $errors = $form->get('email')->getErrors());
        self::assertSame('invalid email test', $errors[0]->getMessage());

        self::assertCount(1, $errors = $form->get('username')->getErrors());
        self::assertSame('invalid username test', $errors[0]->getMessage());

        self::assertCount(0, $form->get('friends')->get('0')->getErrors());

        self::assertCount(1, $errors = $form->get('friends')->get('1')->getErrors());
        self::assertSame('invalid friend test', $errors[0]->getMessage());
    }

    /**
     * @test
     */
    public function itShouldUseTheTranslator(): void
    {
        $translator = new class implements TranslatorInterface {
            use TranslatorTrait {
                trans as traitTrans;
            }

            public function trans(
                ?string $id,
                array $parameters = [],
                string $domain = null,
                string $locale = null
            ): string {
                TestCase::assertSame('translationDomain', $domain);

                return $this->traitTrans('translated ' . $id, $parameters, $domain, $locale);
            }
        };

        $form = $this->createFormAndMapViolations(
            new FormViolationMapper('custom error', $translator, 'translationDomain')
        );

        self::assertCount(2, $errors = $form->getErrors());
        self::assertSame('translated custom error', $errors[0]->getMessage());
        self::assertSame('translated invalid nofield test', $errors[1]->getMessage());

        self::assertCount(1, $errors = $form->get('email')->getErrors());
        self::assertSame('translated invalid email test', $errors[0]->getMessage());

        self::assertCount(1, $errors = $form->get('username')->getErrors());
        self::assertSame('translated invalid username test', $errors[0]->getMessage());

        self::assertCount(0, $form->get('friends')->get('0')->getErrors());

        self::assertCount(1, $errors = $form->get('friends')->get('1')->getErrors());
        self::assertSame('translated invalid friend test', $errors[0]->getMessage());
    }

    private function itShouldNotAddTheErrorMessage(): void
    {
        $form = $this->createFormAndMapViolations(new FormViolationMapper(''));

        self::assertCount(1, $errors = $form->getErrors());
        self::assertSame('invalid nofield test', $errors[1]->getMessage());

        $form = $this->createFormAndMapViolations(new FormViolationMapper(null));

        self::assertCount(1, $errors = $form->getErrors());
        self::assertSame('invalid nofield test', $errors[1]->getMessage());
    }

    private function createFormAndMapViolations(FormViolationMapper $formViolationMapper): FormInterface
    {
        $form = (new FormFactoryBuilder(true))->getFormFactory()->createBuilder(data: ['friends' => ['', '']])
            ->add('email', TextType::class)
            ->add('username', TextType::class)
            ->add('friends', CollectionType::class)
            ->getForm();

        $formViolationMapper->mapViolations($form, [
            new Violation('email', 'invalid email {{ value }}', [
                new ViolationParameter('value', 'test')
            ]),
            new Violation('username', 'invalid username {{ value }}', [
                new ViolationParameter('value', 'test')
            ]),
            new Violation('friends[1]', 'invalid friend {{ value }}', [
                new ViolationParameter('value', 'test')
            ]),
            new Violation('nofield', 'invalid nofield {{ value }}', [
                new ViolationParameter('value', 'test')
            ])
        ]);

        return $form;
    }
}
