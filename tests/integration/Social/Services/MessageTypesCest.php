<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use IntegrationTester;
use Kanvas\Packages\Social\MessageTypes;
use Kanvas\Packages\Social\Models\MessageTypes as ModelsMessageTypes;
use Kanvas\Packages\Test\Support\Models\Users;

class MessageTypesCest
{
    public ModelsMessageTypes $messageType;

    /**
     * Create a message type for tests.
     *
     * @return void
     */
    protected function createMessageType() : void
    {
        $this->messageType = MessageTypes::create(Users::findFirst(1), 'test', 'For Testing');
    }

    /**
     * Test add message type.
     *
     * @param UnitTester $I
     *
     * @return void
     */
    public function addMessageType(IntegrationTester $I) : void
    {
        $messageType = MessageTypes::create(Users::findFirst(1), 'tweet', 'Para probar');

        $I->assertEquals('tweet', $messageType->verb);
    }

    /**
     * Test getTypeByVerb.
     *
     * @param IntegrationTester $I
     * @before createMessageType
     *
     * @return void
     */
    public function getTypeByVerb(IntegrationTester $I) : void
    {
        $messageType = ModelsMessageTypes::getTypeByVerb('test');

        $I->assertEquals($this->messageType->verb, $messageType->verb);
    }

    /**
     * Test get Message Type by its Id.
     *
     * @param IntegrationTester $I
     * @before createMessageType
     *
     * @return void
     */
    public function getMessageType(IntegrationTester $I) : void
    {
        $messageType = ModelsMessageTypes::getByUuid($this->messageType->uuid);

        $I->assertEquals($this->messageType->getId(), $messageType->getId());
    }

    /**
     * Test delete Message Type.
     *
     * @param IntegrationTester $I
     * @before createMessageType
     *
     * @return void
     */
    public function deleteMessageType(IntegrationTester $I) : void
    {
        $I->assertTrue(
            MessageTypes::delete(
                $this->messageType
            )
        );
    }
}
