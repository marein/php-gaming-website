<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Chat\Application;

use Gaming\Chat\Application\ChatGateway;
use Gaming\Chat\Application\ChatId;
use Gaming\Chat\Application\ChatService;
use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Chat\Application\Event\ChatInitiated;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Chat\Application\Exception\AuthorNotAllowedException;
use Gaming\Chat\Application\Exception\ChatAlreadyExistsException;
use Gaming\Chat\Application\Exception\EmptyMessageException;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\EventStore\InMemoryEventStore;
use Gaming\Common\IdempotentStorage\InMemoryIdempotentStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ChatServiceTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInitiateChatExactlyOnce(): void
    {
        $expectedChatId = ChatId::generate();
        $authors = ['authorId1', 'authorId2'];

        $chatGateway = $this->createMock(ChatGateway::class);
        $chatGateway
            ->expects($this->exactly(2))
            ->method('nextIdentity')
            ->willReturnOnConsecutiveCalls($expectedChatId, ChatId::generate());
        $chatGateway
            ->expects($this->exactly(2))
            ->method('create')
            ->with($expectedChatId, $authors)
            ->willReturnOnConsecutiveCalls(null, $this->throwException(new ChatAlreadyExistsException()));

        $eventStore = new InMemoryEventStore(new MockClock());

        $chatService = new ChatService(
            $chatGateway,
            $eventStore,
            new MockClock(),
            new InMemoryIdempotentStorage()
        );

        $chatId = $chatService->initiateChat(
            new InitiateChatCommand('idempotency-key', $authors)
        );
        $this->assertSame($expectedChatId->toString(), $chatId);

        // Retry with the same idempotency key. The chat id should be the same.
        $chatId = $chatService->initiateChat(
            new InitiateChatCommand('idempotency-key', $authors)
        );
        $this->assertSame($expectedChatId->toString(), $chatId);

        $storedEvents = $eventStore->byAggregateId($expectedChatId->toString());
        self::assertCount(1, $storedEvents);

        assert($storedEvents[0]->domainEvent() instanceof ChatInitiated);
        self::assertEquals($expectedChatId->toString(), $storedEvents[0]->domainEvent()->aggregateId());
    }

    /**
     * @test
     */
    public function itShouldThrowMessageEmptyException(): void
    {
        $this->expectException(EmptyMessageException::class);

        $chatService = new ChatService(
            $this->createMock(ChatGateway::class),
            new InMemoryEventStore(new MockClock()),
            new MockClock(),
            new InMemoryIdempotentStorage()
        );

        // Test also if trim is performed.
        $chatService->writeMessage(
            new WriteMessageCommand(
                ChatId::generate()->toString(),
                'authorId',
                '   '
            )
        );
    }

    /**
     * @test
     */
    public function itShouldAllowOnlyAuthorsAssignedToChat(): void
    {
        $this->expectException(AuthorNotAllowedException::class);

        $chatId = ChatId::generate();
        $assignedAuthors = json_encode(['authorId1', 'authorId2'], JSON_THROW_ON_ERROR);

        $chatGateway = $this->createMock(ChatGateway::class);
        $chatGateway
            ->expects($this->once())
            ->method('byId')
            ->with($chatId)
            ->willReturn(['chatId' => $chatId, 'authors' => $assignedAuthors]);

        $chatService = new ChatService(
            $chatGateway,
            new InMemoryEventStore(new MockClock()),
            new MockClock(),
            new InMemoryIdempotentStorage()
        );

        $chatService->writeMessage(
            new WriteMessageCommand(
                $chatId->toString(),
                'authorId3',
                'message'
            )
        );
    }

    /**
     * @test
     */
    public function itShouldWriteMessage(): void
    {
        $clock = new MockClock();

        $chatId = ChatId::generate();
        $authorId = 'authorId';
        $message = 'message';
        $writtenAt = $clock->now();
        $messageId = 7;

        $chatGateway = $this->createMock(ChatGateway::class);
        $chatGateway
            ->expects($this->once())
            ->method('byId')
            ->with($chatId)
            ->willReturn(['chatId' => $chatId, 'authors' => '[]']);
        $chatGateway
            ->expects($this->once())
            ->method('createMessage')
            ->with($chatId, $authorId, $message)
            ->willReturn($messageId);

        $eventStore = new InMemoryEventStore($clock);

        $chatService = new ChatService(
            $chatGateway,
            $eventStore,
            $clock,
            new InMemoryIdempotentStorage()
        );

        $chatService->writeMessage(
            new WriteMessageCommand(
                $chatId->toString(),
                $authorId,
                $message
            )
        );

        $storedEvents = $eventStore->byAggregateId($chatId->toString());
        self::assertCount(1, $storedEvents);

        assert($storedEvents[0]->domainEvent() instanceof MessageWritten);
        self::assertEquals($chatId->toString(), $storedEvents[0]->domainEvent()->aggregateId());
        self::assertEquals($messageId, $storedEvents[0]->domainEvent()->messageId());
        self::assertEquals($authorId, $storedEvents[0]->domainEvent()->authorId());
        self::assertEquals($message, $storedEvents[0]->domainEvent()->message());
        self::assertEquals($writtenAt, $storedEvents[0]->domainEvent()->writtenAt());
    }

    /**
     * @test
     */
    public function itShouldReturnMessages(): void
    {
        $chatId = ChatId::generate();
        $authorId = 'authorId';
        $offset = 0;
        $limit = 10;

        $chatGateway = $this->createMock(ChatGateway::class);
        $chatGateway
            ->expects($this->once())
            ->method('messages')
            ->with($chatId, $authorId, $offset, $limit)
            ->willReturn(['a', 'a', 'a']);

        $chatService = new ChatService(
            $chatGateway,
            new InMemoryEventStore(new MockClock()),
            new MockClock(),
            new InMemoryIdempotentStorage()
        );

        $messages = $chatService->messages(
            new MessagesQuery(
                $chatId->toString(),
                $authorId,
                $offset,
                $limit
            )
        );

        $this->assertSame(['a', 'a', 'a'], $messages);
    }
}
