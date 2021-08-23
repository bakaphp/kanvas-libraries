<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use IntegrationTester;
use Kanvas\Packages\Social\Models\Reactions as ModelsReactions;
use Kanvas\Packages\Social\Reactions;
use Kanvas\Packages\Test\Support\Models\Users;

class ReactionsCest
{
    public ModelsReactions $reaction;

    /**
     * Create a reaction for testing.
     *
     * @return void
     */
    protected function reactionCreation() : void
    {
        $this->reaction = Reactions::createReaction('test-reaction', Users::findFirst(1), '💀');
    }

    /**
     * Test create Reactions.
     *
     * @param UnitTester $I
     *
     * @return void
     */
    public function createReaction(IntegrationTester $I) : void
    {
        $reaction = Reactions::createReaction('test', Users::findFirst(1), '😎');

        $I->assertEquals('test', $reaction->name);
    }

    /**
     * Test get Reaction by its name.
     *
     * @param IntegrationTester $I
     * @before reactionCreation
     *
     * @return void
     */
    public function getReactionByName(IntegrationTester $I) : void
    {
        $reaction = Reactions::getReactionByName('test-reaction', Users::findFirst(1));

        $I->assertEquals('test-reaction', $reaction->name);
    }

    /**
     * Test get Reaction by Emoji.
     *
     * @param IntegrationTester $I
     * @before reactionCreation
     *
     * @return void
     */
    public function getReactionByEmoji(IntegrationTester $I) : void
    {
        $reaction = Reactions::getReactionByEmoji('💀', Users::findFirst(1));

        $I->assertEquals('💀', $reaction->icon);
    }

    /**
     * Test get Reactions Emojies.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getReactionsEmojis(IntegrationTester $I) : void
    {
        $reactionsEmojies = Reactions::getReactionsEmojis();

        $I->assertNotNull($reactionsEmojies[0]->icon);
    }

    /**
     * Test edit Reactions Emojies.
     *
     * @param IntegrationTester $I
     * @before reactionCreation
     *
     * @return void
     */
    public function editReaction(IntegrationTester $I) : void
    {
        $reaction = Reactions::editReaction($this->reaction, 'test-edited-reaction');

        $I->assertEquals('test-edited-reaction', $reaction->name);
    }

    /**
     * Test reaction delete.
     *
     * @param IntegrationTester $I
     * @before reactionCreation
     *
     * @return void
     */
    public function deleteReaction(IntegrationTester $I) : void
    {
        $I->assertTrue(Reactions::deleteReaction($this->reaction));
    }
}
