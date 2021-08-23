<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use IntegrationTester;
use Kanvas\Packages\Social\Follow;
use Kanvas\Packages\Test\Support\Models\Tag;
use Kanvas\Packages\Test\Support\Models\Users;

class FollowsCest
{


    /**
     * Test follow service method user follow.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function follow(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        for ($i = 1; $i < 10; $i++) {
            $userFollow = Users::findFirst(1);
            $userFollow->id = $i + 1;

            if (!$user->isFollowing($userFollow)) {
                $I->assertTrue($user->follow($userFollow));
            }
        }

        for ($i = 1; $i < 10; $i++) {
            $tag1 = new Tag();
            $tag1->id = $i;

            if (!$user->isFollowing($tag1)) {
                $I->assertTrue($user->follow($tag1));
            }
        }

        if (!$userFollow->isFollowing($user)) {
            $userFollow->follow($user);
        }
    }

    /**
     * Test Users follows by entities.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getUserFollows(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $user->id = 1;

        $tag1 = new Tag();
        $tag1->id = 1;
        $follows = Follow::getFollowsByUser($user, $tag1)->toArray();
        $I->assertNotNull($follows[0]['id']);

        $I->assertEquals($follows[0]['entity_namespace'], get_class(new Tag()));
    }

    public function unfollow(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        for ($i = 1; $i < 5; $i++) {
            $userFollow = Users::findFirst(1);
            $userFollow->id = $i + 1;

            if ($user->isFollowing($userFollow)) {
                $I->assertTrue($user->unFollow($userFollow));
            }
        }
    }

    public function isFollowing(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        for ($i = 1; $i < 5; $i++) {
            $userFollow = Users::findFirst(1);
            $userFollow->id = $i + 1;

            $I->assertIsBool($user->isFollowing($userFollow));
        }
    }

    public function followingCount(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        $I->assertGreaterThan(1, $user->getTotalFollowing(Users::class));
    }

    public function followersCount(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        $I->assertGreaterThan(0, $user->getTotalFollowers());
    }

    public function followingCountOfEntity(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        $I->assertGreaterThan(1, $user->getTotalFollowing(Tag::class));
    }

    public function followersCountOfEntity(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $tag = new Tag();
        $tag->id = 1;

        $I->assertGreaterThan(0, Follow::getTotalFollowers($tag));
    }
}
