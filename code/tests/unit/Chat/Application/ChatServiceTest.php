<?php

namespace Gambling\Common\Application;

use Gambling\Chat\Application\ChatGateway;
use Gambling\Chat\Application\ChatService;
use Gambling\Chat\Application\Exception\AuthorNotAllowedException;
use Gambling\Chat\Application\Exception\EmptyMessageException;
use Gambling\Common\EventStore\EventStore;
use Gambling\Common\EventStore\InMemoryEventStore;
use PHPUnit\Framework\TestCase;

final class ChatServiceTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInitiateChat(): void
    {
        $generatedChatId = 'chatId';
        $ownerId = 'ownerId';
        $authors = ['authorId1', 'authorId2'];

        $applicationLifeCycle = new InvokeApplicationLifeCycle();
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = new InMemoryEventStore();

        $chatGateway
            ->expects($this->once())
            ->method('create')
            ->with($ownerId, $authors)
            ->willReturn($generatedChatId);

        /** @var ChatGateway $chatGateway */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        $chatId = $chatService->initiateChat($ownerId, $authors);
        $storedEvent = $eventStore->storedEventsSince(0, 1)[0];

        // Test directly against the stored event. Mocking isn't possible due the
        // "new \DateTimeImmutable()" call inside events.
        $this->assertSame('chat.chat-initiated', $storedEvent->name());
        $this->assertSame(
            json_encode(
                ['chatId' => $generatedChatId, 'ownerId' => $ownerId]
            ),
            $storedEvent->payload()
        );
        $this->assertSame($generatedChatId, $chatId);
    }

    /**
     * @test
     */
    public function itShouldThrowMessageEmptyException(): void
    {
        $this->expectException(EmptyMessageException::class);

        $applicationLifeCycle = $this->createMock(ApplicationLifeCycle::class);
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        /** @var ApplicationLifeCycle $applicationLifeCycle */
        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        // Test also if trim is performed.
        $chatService->writeMessage('chatId', 'authorId', '   ');
    }

    /**
     * @test
     */
    public function itShouldAllowOnlyAuthorsAssignedToChat(): void
    {
        $this->expectException(AuthorNotAllowedException::class);

        $chatId = 'chatId';
        $assignedAuthors = json_encode(['authorId1', 'authorId2']);

        $applicationLifeCycle = $this->createMock(ApplicationLifeCycle::class);
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        $chatGateway
            ->expects($this->once())
            ->method('byId')
            ->with($chatId)
            ->willReturn(['chatId' => $chatId, 'authors' => $assignedAuthors]);

        /** @var ApplicationLifeCycle $applicationLifeCycle */
        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        // Test also if trim is performed.
        $chatService->writeMessage($chatId, 'authorId3', 'message');
    }

    /**
     * @test
     */
    public function itShouldWriteMessage(): void
    {
        $chatId = 'chatId';
        $authorId = 'authorId';
        $ownerId = 'ownerId';
        $message = 'message';
        $messageId = 7;

        $applicationLifeCycle = new InvokeApplicationLifeCycle();
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = new InMemoryEventStore();

        $chatGateway
            ->expects($this->once())
            ->method('byId')
            ->with($chatId)
            ->willReturn(['chatId' => $chatId, 'authors' => '[]', 'ownerId' => $ownerId]);

        $chatGateway
            ->expects($this->once())
            ->method('createMessage')
            ->with($chatId, $authorId, $message)
            ->willReturn($messageId);

        /** @var ChatGateway $chatGateway */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        $chatService->writeMessage($chatId, $authorId, $message);
        $storedEvent = $eventStore->storedEventsSince(0, 1)[0];

        // Test directly against the stored event. Mocking isn't possible due the
        // "new \DateTimeImmutable()" call inside events.
        $this->assertSame('chat.message-written', $storedEvent->name());
        $this->assertSame(
            json_encode(
                [
                    'chatId'    => $chatId,
                    'messageId' => $messageId,
                    'ownerId'   => $ownerId,
                    'authorId'  => $authorId,
                    'message'   => $message
                ]
            ),
            $storedEvent->payload()
        );
    }

    /**
     * @test
     */
    public function itShouldReturnMessages(): void
    {
        $chatId = 'chatId';
        $authorId = 'authorId';
        $offset = 0;
        $limit = 10;

        $applicationLifeCycle = $this->createMock(ApplicationLifeCycle::class);
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        $chatGateway
            ->expects($this->once())
            ->method('messages')
            ->with($chatId, $authorId, $offset, $limit)
            ->willReturn(['a', 'a', 'a']);

        /** @var ApplicationLifeCycle $applicationLifeCycle */
        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        $messages = $chatService->messages($chatId, $authorId, $offset, $limit);

        $this->assertSame(['a', 'a', 'a'], $messages);
    }
}
