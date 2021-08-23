<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Comments\Models;

use Kanvas\Packages\Social\Comments\Model;
use Kanvas\Packages\Social\ElasticDocuments\Messages as ElasticMessagesDocument;
use Kanvas\Packages\Social\Jobs\ElasticMessages;
use Phalcon\Di;

class Messages extends Model
{
    public int $message_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('message_comments');

        $this->belongsTo(
            'message_id',
            Messages::class,
            'id',
            [
                'reusable' => true,
                'alias' => 'messages',
                'params' => [
                    'conditions' => 'is_deleted = 0'
                ]
            ]
        );
    }

    /**
     * Create a comment for a message.
     *
     * @param string $messageId
     * @param string $message
     *
     * @return MessageComments
     */
    public function reply(string $message) : self
    {
        $comment = new self();
        $comment->message_id = $this->message_id;
        $comment->apps_id = Di::getDefault()->get('app')->getId();
        $comment->companies_id = Di::getDefault()->get('userData')->getDefaultCompany()->getId();
        $comment->users_id = Di::getDefault()->get('userData')->getId();
        $comment->message = $message;
        $comment->parent_id = $this->getParentId();
        $comment->saveOrFail();

        return $comment;
    }


    /**
     * Verify if this comment has message.
     *
     * @param Messages $message
     *
     * @return bool
     */
    public function hasMessage(Messages $message) : bool
    {
        return $this->message_id == $message->getId();
    }

    /**
     * After update.
     *
     * @return void
     */
    public function afterSave()
    {
        parent::afterSave();
        $elasticMessage = new ElasticMessagesDocument();
        $elasticMessage->updateCommentsCount($this->messages);
        ElasticMessages::dispatch($this->messages);
    }
}
