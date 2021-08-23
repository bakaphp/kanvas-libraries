<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

use Baka\Contracts\Auth\UserInterface;
use Canvas\Contracts\EventManagerAwareTrait;
use Phalcon\Mvc\ModelInterface;

class UsersInteractions extends BaseModel
{
    use EventManagerAwareTrait;


    public int $users_id;
    public int $entity_id;
    public string $entity_namespace;
    public int $interactions_id;

    const LIKE = 'like';
    const SAVE = 'save';
    const COMMENT = 'comment';
    const REPLIED = 'reply';
    const FOLLOWING = 'follow';

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
                'alias' => 'entityData',
                'params' => [
                    'conditions' => 'is_deleted = 0'
                ]
            ]
        );
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('users_interactions');
    }

    /**
     * Given the entity and its interaction check if user interact with it.
     *
     * @param UserInterface $user
     * @param ModelInterface $entity
     * @param Interactions $interaction
     *
     * @return self|null
     */
    public static function getByEntityInteraction(UserInterface $user, ModelInterface $entity, Interactions $interaction) : ?self
    {
        return self::findFirst([
            'conditions' => 'users_id = :userId: 
                                AND interactions_id = :interactionId: 
                                AND entity_namespace = :namespace: 
                                AND entity_id = :entityId:',
            'bind' => [
                'userId' => $user->getId(),
                'interactionId' => $interaction->getId(),
                'namespace' => get_class($entity),
                'entityId' => $entity->getId(),
            ]
        ]);
    }

    /**
     * After create.
     *
     * @return void
     */
    public function afterCreate()
    {
        if (method_exists(get_parent_class($this), 'afterCreate')) {
            parent::afterCreate();
        }
        $this->fire('kanvas.social.interactions:afterCreate', $this);
    }

    /**
     * After create.
     *
     * @return void
     */
    public function afterSave()
    {
        if (method_exists(get_parent_class($this), 'afterSave')) {
            parent::afterSave();
        }
        $this->fire('kanvas.social.interactions:afterSave', $this);
    }
}
