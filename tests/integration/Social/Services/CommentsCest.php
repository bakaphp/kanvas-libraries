<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use Codeception\Lib\Di;
use IntegrationTester;
use Kanvas\Packages\Social\Models\MessageComments;
use Kanvas\Packages\Social\Models\Messages;
use Kanvas\Packages\Social\Services\Comments;
use Kanvas\Packages\Test\Support\Models\Users;

class CommentsCest
{
    public $comment;

    /**
     * Get the first comment
     *
     * @return void
     */
    protected function getCommentData(): void
    {
        $this->comment = MessageComments::findFirst();
    }
    
    /**
     * Test add comment
     *
     * @param UnitTester $I
     * @return void
     */
    public function addComment(IntegrationTester $I): void
    {
        $feed = Messages::findFirst();
        $comment = Comments::add($feed->getId(), 'test-text');

        $I->assertEquals('test-text', $comment->message);
    }

    /**
     * Test comment edit
     *
     * @param IntegrationTester $I
     * @before getCommentData
     * @return void
     */
    public function editComment(IntegrationTester $I): void
    {
        $comment = Comments::edit((string) $this->comment->getId(), 'edited-test-text');

        $I->assertEquals('edited-test-text', $comment->message);
    }

    /**
     * Test get Comment
     *
     * @param IntegrationTester $I
     * @before getCommentData
     * @return void
     */
    public function getComment(IntegrationTester $I): void
    {
        $comment = Comments::get((string) $this->comment->getId());

        $I->assertNotNull($comment->getId());
    }

    /**
     * Test reply comment
     *
     * @param IntegrationTester $I
     * @before getCommentData
     * @return void
     */
    public function replyComment(IntegrationTester $I): void
    {
        $reply = Comments::reply($this->comment->getId(), 'reply-test');

        $I->assertEquals($reply->message, 'reply-test');
        $I->assertEquals($reply->parent_id, $this->comment->getId());
    }

    /**
     * Test edit comment
     *
     * @param IntegrationTester $I
     * @before getCommentData
     * @return void
     */
    public function deleteComment(IntegrationTester $I): void
    {
        $I->assertTrue(
            Comments::delete(
                (string) $this->comment->getId(),
                new Users()
            )
        );
    }
}
