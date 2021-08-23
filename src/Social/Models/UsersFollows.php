<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

use Baka\Contracts\Auth\UserInterface;
use Canvas\Contracts\EventManagerAwareTrait;
use Canvas\Models\Users;
use Phalcon\Mvc\ModelInterface;

class UsersFollows extends BaseModel
{
    use EventManagerAwareTrait;

    public int $users_id;
    public int $entity_id;
    public ?int $companies_id = null;
    public ?int $companies_branches_id = null;
    public string $entity_namespace;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('users_follows');

        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            [
                'alias' => 'user',
            ]
        );
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
                'alias' => 'entityData'
            ]
        );
    }

    /**
     * Remove the user interaction by update is_deleted.
     *
     * @return void
     */
    public function unFollow() : bool
    {
        if ($this->is_deleted) {
            $this->is_deleted = 0;
            $this->saveOrFail();

            if (method_exists($this->user, 'increment')) {
                $this->user->increment(Interactions::FOLLOWING, $this->entity_namespace);
            }
        } elseif (!$this->is_deleted) {
            $this->is_deleted = 1;
            $this->saveOrFail();

            if (method_exists($this->user, 'decrees')) {
                $this->user->decrees(Interactions::FOLLOWING, $this->entity_namespace);
            }
        }

        return $this->isFollowing();
    }

    /**
     * Get the User following the entity.
     *
     * @param UserInterface $user
     * @param ModelInterface $entity
     *
     * @return self|null
     */
    public static function getByUserAndEntity(UserInterface $user, ModelInterface $entity) : ?self
    {
        return UsersFollows::findFirst([
            'conditions' => 'users_id = :user_id: AND entity_id = :entity_id: AND entity_namespace = :entity:',
            'bind' => [
                'user_id' => $user->getId(),
                'entity' => get_class($entity),
                'entity_id' => $entity->getId()
            ]
        ]);
    }

    /**
     * Lets you know if the user is following the entity.
     *
     * @return bool
     */
    public function isFollowing() : bool
    {
        return !$this->is_deleted;
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
        $this->fire('kanvas.social.follow:afterCreate', $this);
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
        $this->fire('kanvas.social.follow:afterSave', $this);
    }
}
