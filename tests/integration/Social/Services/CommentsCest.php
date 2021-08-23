<?php

namespace Kanvas\Packages\Tests\Integration\Social\Service;

use Canvas\Models\SystemModules;
use IntegrationTester;
use Kanvas\Packages\Social\Comments;
use Kanvas\Packages\Social\Enums\Interactions as EnumsInteractions;
use Kanvas\Packages\Social\Interactions;
use Kanvas\Packages\Social\Messages as MessagesService;
use Kanvas\Packages\Social\MessageTypes;
use Kanvas\Packages\Social\Models\MessageComments;
use Kanvas\Packages\Social\Models\Messages;
use Kanvas\Packages\Social\Reactions;
use Kanvas\Packages\Test\Support\Models\Users;

class CommentsCest
{
    public MessageComments $comment;

    /**
     * Get the first comment.
     *
     * @return void
     */
    protected function getCommentData() : void
    {
        $this->comment = MessageComments::findFirst('is_deleted = 0');
    }

    /**
     * Test add comment.
     *
     * @param UnitTester $I
     *
     * @return void
     */
    public function addComment(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        //Add new SystemModule for Messages
        $systemModule = SystemModules::findFirstOrCreate([
            'conditions' => 'apps_id = :apps_id: and model_name = :model_name: and is_deleted = 0',
            'bind' => [
                'apps_id' => 1,
                'model_name' => 'Kanvas\Packages\Social\Models\Messages'
            ]], [
                'name' => 'Messages',
                'slug' => 'messages',
                'apps_id' => 1,
                'model_name' => 'Kanvas\Packages\Social\Models\Messages',
                'date' => date('Y-m-d H:i:s')
            ]);

        //Create a new message type
        MessageTypes::create($user, 'comments', 'Test Type');

        $text = [
            'text' => 'Test some messages'
        ];

        //Create a new Message for the comment
        $feed = MessagesService::create($user, 'comments', $text);
        $comment = Comments::add($feed->getId(), 'test-text', $user);

        $I->assertEquals('test-text', $comment->message);
    }

    /**
     * Test comment edit.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function editComment(IntegrationTester $I) : void
    {
        $comment = Comments::edit((string) $this->comment->getId(), 'edited-test-text');

        $I->assertEquals('edited-test-text', $comment->message);
    }

    /**
     * Test get Comment.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function getComment(IntegrationTester $I) : void
    {
        $comment = Comments::getById($this->comment->getId());

        $I->assertNotNull($comment->getId());
    }

    /**
     * Test reply comment.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function replyComment(IntegrationTester $I) : void
    {
        $reply = Comments::reply($this->comment->getId(), 'reply-test');

        $I->assertEquals($reply->message, 'reply-test');
        $I->assertEquals($reply->parent_id, $this->comment->getId());
    }

    /**
     * Test edit comment.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function deleteComment(IntegrationTester $I) : void
    {
        $I->assertTrue(
            Comments::delete(
                (string) $this->comment->getId(),
                new Users()
            )
        );
    }

    /**
     * Test comments Reactions.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function commentReaction(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);
        $I->assertFalse(Reactions::addMessageReaction('confuse', $user, $this->comment));
        $I->assertFalse(Reactions::addMessageReaction('☺', $user, $this->comment));

        $I->assertTrue(Reactions::addMessageReaction('confuse', $user, $this->comment));
        $I->assertTrue(Reactions::addMessageReaction('☺', $user, $this->comment));
    }

    /**
     * Test Users Comments Interaction.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function messageInteraction(IntegrationTester $I) : void
    {
        $user = Users::findFirst(1);

        $I->assertFalse(
            Interactions::add($user, $this->comment, EnumsInteractions::REACT)
        );
    }

    /**
     * Test method to get comments from a message.
     *
     * @param IntegrationTester $I
     * @before getCommentData
     *
     * @return void
     */
    public function getCommentsFromMessage(IntegrationTester $I) : void
    {
        $message = $this->comment->messages;
        $comments = Comments::getCommentsFromMessage($message);

        $I->assertNotEmpty($comments->toArray());
    }
}
