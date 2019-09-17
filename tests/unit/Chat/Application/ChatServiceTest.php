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
use Gaming\Chat\Application\Exception\EmptyMessageException;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\Clock\Clock;
use Gaming\Common\EventStore\EventStore;
use PHPUnit\Framework\TestCase;

final class ChatServiceTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInitiateChat(): void
    {
        Clock::instance()->freeze();

        $generatedChatId = ChatId::generate();
        $ownerId = 'ownerId';
        $authors = ['authorId1', 'authorId2'];

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
            $chatGateway,
            $eventStore
        );

        $chatId = $chatService->initiateChat(
            new InitiateChatCommand($ownerId, $authors)
        );
        $this->assertSame($generatedChatId->toString(), $chatId);

        Clock::instance()->resume();
    }

    /**
     * @test
     */
    public function itShouldThrowMessageEmptyException(): void
    {
        $this->expectException(EmptyMessageException::class);

        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $chatGateway,
            $eventStore
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
        $assignedAuthors = json_encode(['authorId1', 'authorId2']);

        $chatGateway = $this->createMock(ChatGateway::class);
        $eventStore = $this->createMock(EventStore::class);

        $chatGateway
            ->expects($this->once())
            ->method('byId')
            ->with($chatId)
            ->willReturn(['chatId' => $chatId, 'authors' => $assignedAuthors]);

        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $chatGateway,
            $eventStore
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
        Clock::instance()->freeze();

        $chatId = ChatId::generate();
        $authorId = 'authorId';
        $ownerId = 'ownerId';
        $message = 'message';
        $writtenAt = Clock::instance()->now();
        $messageId = 7;

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
            $chatGateway,
            $eventStore
        );

        $chatService->writeMessage(
            new WriteMessageCommand(
                $chatId->toString(),
                $authorId,
                $message
            )
        );

        Clock::instance()->resume();
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
        $eventStore = $this->createMock(EventStore::class);

        $chatGateway
            ->expects($this->once())
            ->method('messages')
            ->with($chatId, $authorId, $offset, $limit)
            ->willReturn(['a', 'a', 'a']);

        /** @var ChatGateway $chatGateway */
        /** @var EventStore $eventStore */
        $chatService = new ChatService(
            $chatGateway,
            $eventStore
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
