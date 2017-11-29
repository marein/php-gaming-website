<?php

namespace Gambling\Common\Application;

use Gambling\Chat\Application\ChatGateway;
use Gambling\Chat\Application\ChatService;
use Gambling\Chat\Application\Event\ChatInitiated;
use Gambling\Chat\Application\Event\MessageWritten;
use Gambling\Chat\Application\Exception\AuthorNotAllowedException;
use Gambling\Chat\Application\Exception\EmptyMessageException;
use Gambling\Common\Clock\Clock;
use Gambling\Common\EventStore\EventStore;
use PHPUnit\Framework\TestCase;

final class ChatServiceTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInitiateChat(): void
    {
        Clock::instance()->freeze();

        $generatedChatId = 'chatId';
        $ownerId = 'ownerId';
        $authors = ['authorId1', 'authorId2'];

        $applicationLifeCycle = new InvokeApplicationLifeCycle();
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        $eventStore
            ->expects($this->once())
            ->method('append')
            ->with(new ChatInitiated($generatedChatId, $ownerId));

        $chatGateway
            ->expects($this->once())
            ->method('create')
            ->with($ownerId, $authors)
            ->willReturn($generatedChatId);

        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        $chatId = $chatService->initiateChat($ownerId, $authors);
        $this->assertSame($generatedChatId, $chatId);

        Clock::instance()->resume();
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

        $chatService->writeMessage($chatId, 'authorId3', 'message');
    }

    /**
     * @test
     */
    public function itShouldWriteMessage(): void
    {
        Clock::instance()->freeze();

        $chatId = 'chatId';
        $authorId = 'authorId';
        $ownerId = 'ownerId';
        $message = 'message';
        $writtenAt = Clock::instance()->now();
        $messageId = 7;

        $applicationLifeCycle = new InvokeApplicationLifeCycle();
        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        $eventStore
            ->expects($this->once())
            ->method('append')
            ->with(new MessageWritten($chatId, $messageId, $ownerId, $authorId, $message, $writtenAt));

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
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $applicationLifeCycle,
            $chatGateway,
            $eventStore
        );

        $chatService->writeMessage($chatId, $authorId, $message);

        Clock::instance()->resume();
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
