<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use IntegrationTester;
use Kanvas\Packages\Social\Models\Tags as ModelsTags;
use Kanvas\Packages\Social\Tags;
use Kanvas\Packages\Test\Support\Models\Users;

class TagsCest
{
    public ModelsTags $tag;

    /**
     * Create a tag for test use.
     *
     * @return void
     */
    protected function createTagTest() : void
    {
        $this->tag = Tags::create(Users::findFirst(1), 'Tag for test');
    }

    /**
     * Test create of tags service.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createTag(IntegrationTester $I) : void
    {
        $tag = Tags::create(Users::findFirst(1), 'Test Tag');

        $I->assertEquals('Test Tag', $tag->name);
    }

    /**
     * Test get Tag from Tag Service.
     *
     * @param IntegrationTester $I
     * @before createTagTest
     *
     * @return void
     */
    public function getTag(IntegrationTester $I) : void
    {
        $tag = Tags::get($this->tag->getId());

        $I->assertEquals($this->tag->getId(), $tag->getId());
    }

    /**
     * Test update from Tags Service.
     *
     * @param IntegrationTester $I
     * @before createTagTest
     *
     * @return void
     */
    public function updateTag(IntegrationTester $I) : void
    {
        $tagUpdate = Tags::update($this->tag, 'Tag Update');

        $I->assertEquals('Tag Update', $tagUpdate->name);
    }

    /**
     * Test delete of tag.
     *
     * @param IntegrationTester $I
     * @before createTagTest
     *
     * @return void
     */
    public function deleteTag(IntegrationTester $I) : void
    {
        $I->assertTrue(
            Tags::delete($this->tag)
        );
    }
}
