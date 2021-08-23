<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Comments\Models;

use Baka\Contracts\Auth\UserInterface;
use Kanvas\Packages\Social\Comments\Model;
use Phalcon\Di;

class Entity extends Model
{
    public ?string $entity_id = null;
    public ?string $entity_namespace = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('entity_comments');
    }

    /**
     * Initialize relationship after fetch
     * since we need entity_namespace info.
     *
     * @return void
     */
    public function afterFetch()
    {
        $this->hasOne(
            'entity_id',
            $this->entity_namespace,
            'id',
            [
                'reusable' => true,
                'alias' => 'entity',
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
    public function reply(string $message, UserInterface $user) : self
    {
        $comment = new self();
        $comment->entity_id = $this->entity_id;
        $comment->apps_id = Di::getDefault()->get('app')->getId();
        $comment->companies_id = $user->getDefaultCompany()->getId();
        $comment->users_id = $user->getId();
        $comment->message = $message;
        $comment->parent_id = $this->getParentId();
        $comment->saveOrFail();

        return $comment;
    }
}
