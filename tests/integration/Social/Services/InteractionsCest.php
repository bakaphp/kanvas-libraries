<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use IntegrationTester;
use Kanvas\Packages\Social\Enums\Interactions as EnumsInteractions;
use Kanvas\Packages\Social\Interactions;
use Kanvas\Packages\Social\Models\Interactions as ModelsInteractions;
use Kanvas\Packages\Social\Models\Tags;
use Kanvas\Packages\Test\Support\Models\Users;

class InteractionsCest
{
    public function likeEntity(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = Tags::findFirst(1);

        $likeTag = $user->likes($tag);

        $I->assertTrue($likeTag);
    }

    public function shareEntity(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = Tags::findFirst(1);

        $likeTag = $user->share($tag);

        $I->assertTrue($likeTag);
    }

    public function addToListEntity(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = Tags::findFirst(1);

        $likeTag = $user->addToList($tag);

        $I->assertTrue($likeTag);
    }

    public function hasLikedEntity(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = Tags::findFirst(1);

        $likeTag = $user->hasInteracted($tag, EnumsInteractions::LIKE);

        $I->assertTrue($likeTag);
    }

    public function removeInteraction(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = Tags::findFirst(1);

        $likeTag = $user->removeInteraction($tag, EnumsInteractions::SAVE);

        $I->assertTrue($likeTag);
    }

    public function getTotalInteractionsByUser(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = Tags::findFirst(1);

        $user->interact($tag, EnumsInteractions::RATE);
        $totalInteractions = $user->getTotalInteractions(Tags::class, EnumsInteractions::RATE);

        $I->assertGreaterOrEquals(1, $totalInteractions);
    }

    public function getTotalInteractionsByEntity(IntegrationTester $I) : void
    {
        $tag = Tags::findFirst(1);

        $totalInteractions = Interactions::getTotalByEntity($tag, EnumsInteractions::RATE);

        $I->assertGreaterOrEquals(1, $totalInteractions);
        $I->assertGreaterOrEquals(1, $tag->getTotalInteractions(EnumsInteractions::RATE));
    }

    /**
     * Test get Reaction by its name.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getInteractionByName(IntegrationTester $I) : void
    {
        $interaction = Interactions::getInteractionByName('react');

        $I->assertInstanceOf(ModelsInteractions::class, $interaction);
        $I->assertNotNull($interaction->id);
    }
}
