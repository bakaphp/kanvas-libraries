<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use Canvas\Models\FileSystem;
use IntegrationTester;
use Kanvas\Packages\Social\Distributions;
use Kanvas\Packages\Social\Enums\Interactions as EnumsInteractions;
use Kanvas\Packages\Social\Interactions;
use Kanvas\Packages\Social\Messages as MessagesService;
use Kanvas\Packages\Social\MessageTypes;
use Kanvas\Packages\Social\Models\Channels;
use Kanvas\Packages\Social\Models\Messages;
use Kanvas\Packages\Social\Reactions;
use Kanvas\Packages\Test\Support\Models\Lead;
use Kanvas\Packages\Test\Support\Models\MessageObject;
use Kanvas\Packages\Test\Support\Models\Users;

class MessagesCest
{
    public Messages $message;
    public Channels $channel;

    /**
     * Get the first comment.
     *
     * @return void
     */
    protected function createMessageType() : void
    {
        MessageTypes::create(Users::findFirst(1), 'memo', 'Test Type');
    }

    /**
     * Create a message for testing.
     *
     * @return void
     */
    protected function messageTestCreation() : void
    {
        $text = [
            'text' => 'Test some messages'
        ];

        $this->message = MessagesService::create(Users::findFirst(1), 'memo', $text);
    }

    /**
     * Create a channel messages for testing.
     *
     * @return void
     */
    protected function channelMessagesDistribution() : void
    {
        $lead = new Lead();

        $this->channel = Channels::findFirstOrCreate(
            [
                'conditions' => 'is_deleted = 0'
            ],
            [
                'name' => 'channel Test',
                'entity_namespace' => new Lead(),
                'entity_id' => $lead->getId()
            ]
        );

        Distributions::sendToChannelFeed($this->channel, $this->message);
    }

    /**
     * Test add comment.
     *
     * @param UnitTester $I
     * @before createMessageType
     *
     * @return void
     */
    public function createMessage(IntegrationTester $I) : void
    {
        $text = [
            'text' => 'This is test text for testing'
        ];

        $feed = MessagesService::create(Users::findFirst(1), 'memo', $text, new MessageObject());

        $I->assertNotNull($feed->getId());
    }

    public function createMessageWithCustomField(IntegrationTester $I) : void
    {
        $text = [
            'text' => 'This is test custom'
        ];

        $feed = MessagesService::create(Users::findFirst(1), 'memo', $text, new MessageObject());
        $feed->set('people', 'max');
        $feed->set('organization', [1, 2, 3]);
        $feed->set('entity', ['nose' => 'abd', '3' => 1]);
        $feed->update();

        $I->assertNotNull($feed->getId());
    }

    public function createMessageWithImage(IntegrationTester $I) : void
    {
        $text = [
            'text' => 'This is test custom'
        ];

        $feed = MessagesService::create(Users::findFirst(1), 'memo', $text, new MessageObject());

        if ($fileSystem = FileSystem::findFirst()) {
            $feed->attach([[
                'id' => 0,
                'file' => $fileSystem,
                'field_name' => 'test',
                'is_deleted' => 0
            ]]);
            $fileSystem->id = 3;
            $feed->attach([[
                'id' => 0,
                'file' => $fileSystem,
                'field_name' => 'test',
                'is_deleted' => 0
            ]]);
            $feed->update();
        }
        $I->assertNotNull($feed->getId());
    }

    public function createMessageWithImageAndCustomFields(IntegrationTester $I) : void
    {
        $text = [
            'text' => 'This is test custom'
        ];

        $feed = MessagesService::create(Users::findFirst(1), 'memo', $text, new MessageObject());
        $feed->set('people', 'max');
        $feed->set('organization', [1, 2, 3]);
        $feed->set('entity', ['nose' => 'abd', '3' => 1]);
        if ($fileSystem = FileSystem::findFirst()) {
            $feed->attach([[
                'id' => 0,
                'file' => $fileSystem,
                'field_name' => 'test',
                'is_deleted' => 0
            ]]);
            $fileSystem->id = 3;
            $feed->attach([[
                'id' => 0,
                'file' => $fileSystem,
                'field_name' => 'test',
                'is_deleted' => 0
            ]]);
            $feed->update();
        }
        $I->assertNotNull($feed->getId());
    }

    /**
     * Create Message by Message Object Test.
     *
     * @param UnitTester $I
     * @before createMessageType
     *
     * @return void
     */
    public function createMessageByObject(IntegrationTester $I) : void
    {
        $text = [
            'text' => 'This is test text for testing'
        ];

        $newMessage = new Messages();
        $newMessage->message = json_encode($text);

        $feed = MessagesService::createByObject(Users::findFirst(1), 'memo', $newMessage, new Messages());

        $I->assertNotNull($feed->getId());
    }

    /**
     * Create Message by Message Object Test.
     *
     * @param UnitTester $I
     * @before createMessageType
     *
     * @return void
     */
    public function getMessageByUuid(IntegrationTester $I) : void
    {
        $message = Messages::findFirst(['order' => 'id desc']);

        $feed = MessagesService::getMessageByUuid($message->uuid);

        $I->assertNotNull($feed->getId());
    }

    /**
     * Test Message Reactions.
     *
     * @param IntegrationTester $I
     * @before messageTestCreation
     *
     * @return void
     */
    public function messageReaction(IntegrationTester $I) : void
    {
        $I->assertFalse(Reactions::addMessageReaction('confuse', Users::findFirst(1), $this->message));

        $I->assertFalse(Reactions::addMessageReaction('☺', Users::findFirst(1), $this->message));

        $I->assertTrue(Reactions::addMessageReaction('confuse', Users::findFirst(1), $this->message));

        $I->assertTrue(Reactions::addMessageReaction('☺', Users::findFirst(1), $this->message));
    }

    /**
     * Test Message Interaction.
     *
     * @param IntegrationTester $I
     * @before messageTestCreation
     *
     * @return void
     */
    public function messageInteraction(IntegrationTester $I) : void
    {
        $I->assertFalse(
            Interactions::add(Users::findFirst(1), $this->message, EnumsInteractions::REACT)
        );
    }

    /**
     * Test message and feed delete.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function deleteMessage(IntegrationTester $I) : void
    {
        $I->assertTrue(
            MessagesService::delete(Messages::findFirst(['order' => 'id desc'])->getId(), Users::findFirst(1))
        );
    }

    /**
     * Create a message for testing.
     *
     * @return void
     */
    protected function messageTestCreationWithChild() : void
    {
        $text = [
            'text' => 'Test some messages'
        ];

        $messageOne = MessagesService::create(Users::findFirst(1), 'memo', $text);
        $messageOneClone = $messageOne;
        $messageTwo = MessagesService::create(Users::findFirst(1), 'memo', $text);
        $messageTwo->setParent($messageOne, 'message-one' . time());

        $I->assertTrue($messageOneClone->updated_at != $messageOne->updated_at);
    }
}
