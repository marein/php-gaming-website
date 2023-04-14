<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus\Integration;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Integration\SymfonyValidatorBus;
use Gaming\Common\Bus\Violation;
use Gaming\Common\Bus\ViolationParameter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SymfonyValidatorBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldForwardMessageToHandlerAndReturnItsValueWithoutException(): void
    {
        $requestMessage = $this->createMessage('Request');
        $responseMessage = $this->createMessage('Response');
        $innerBus = $this->createMock(Bus::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $innerBus
            ->expects($this->once())
            ->method('handle')
            ->with($requestMessage)
            ->willReturn($responseMessage);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));

        $bus = new SymfonyValidatorBus(
            $innerBus,
            $validator
        );
        $response = $bus->handle($requestMessage);

        $this->assertSame('Response', $response->value);
    }

    /**
     * @test
     */
    public function itShouldThrowApplicationExceptionWithViolationsOnError(): void
    {
        $this->expectException(ApplicationException::class);

        $requestMessage = $this->createMessage('Request');
        $innerBus = $this->createMock(Bus::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $innerBus
            ->expects($this->never())
            ->method('handle');

        $constraintViolationList = $this->createConstraintViolationList(
            $this->createConstraintViolation(
                'value',
                'limit_exceeded',
                [
                    '{{ limit }}' => 10
                ]
            ),
            $this->createConstraintViolation(
                'value',
                'not_blank',
                []
            ),
            $this->createConstraintViolation(
                'anotherValue',
                'not_blank',
                []
            )
        );

        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($constraintViolationList);

        /** @var Bus $innerBus */
        /** @var ValidatorInterface $validator */
        $bus = new SymfonyValidatorBus(
            $innerBus,
            $validator
        );

        try {
            $bus->handle($requestMessage);
        } catch (ApplicationException $e) {
            $this->assertEquals(
                [
                    new Violation(
                        'value',
                        'limit_exceeded',
                        [
                            new ViolationParameter('limit', 10)
                        ]
                    ),
                    new Violation('value', 'not_blank', []),
                    new Violation('anotherValue', 'not_blank', [])
                ],
                $e->violations()
            );
            throw $e;
        }
    }

    private function createMessage(string $value): object
    {
        $message = new class () {
            public $value;
        };

        $message->value = $value;

        return $message;
    }

    /**
     * @param ConstraintViolationInterface[] $constraintViolations
     */
    private function createConstraintViolationList(
        ConstraintViolationInterface ...$constraintViolations
    ): ConstraintViolationListInterface {
        return new ConstraintViolationList($constraintViolations);
    }

    private function createConstraintViolation(
        string $propertyPath,
        string $message,
        array $context
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            '',
            $message,
            $context,
            '',
            $propertyPath,
            ''
        );
    }
}
