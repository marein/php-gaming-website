<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus\Integration;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Integration\SymfonyValidatorBus;
use Gaming\Common\Bus\Request;
use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
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
    public function itShouldForwardRequestToHandlerAndReturnItsValue(): void
    {
        $request = $this->createRequest('Request');
        $response = $this->createRequest('Response');
        $innerBus = $this->createMock(Bus::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $innerBus
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));

        $bus = new SymfonyValidatorBus(
            $innerBus,
            $validator
        );

        $this->assertSame($response, $bus->handle($request));
    }

    /**
     * @test
     */
    public function itShouldThrowDomainExceptionWithViolationsOnError(): void
    {
        $this->expectException(DomainException::class);

        $request = $this->createRequest('Request');
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
            $bus->handle($request);
        } catch (DomainException $e) {
            $this->assertEquals(
                [
                    new Violation('limit_exceeded', [new ViolationParameter('limit', 10)], 'value'),
                    new Violation('not_blank', [], 'value'),
                    new Violation('not_blank', [], 'anotherValue')
                ],
                iterator_to_array($e->violations)
            );
            throw $e;
        }
    }

    private function createRequest(string $value): Request
    {
        return new class ($value) implements Request {
            public function __construct(
                public readonly string $value
            ) {
            }
        };
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
            $message,
            '',
            $context,
            '',
            $propertyPath,
            ''
        );
    }
}
