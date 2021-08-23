<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Contracts\Follows;

use Canvas\Models\Users as CanvasUsers;
use Kanvas\Packages\Social\Follow;
use Kanvas\Packages\Social\Models\UsersFollows;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;

trait FollowersTrait
{
    /**
     * Get the total of following of the user.
     *
     * @return int
     */
    public function getTotalFollowing(string $entityNamespace = null) : int
    {
        if (is_null($entityNamespace)) {
            $entityNamespace = CanvasUsers::class;
        }

        return Follow::getTotalFollowing($this, $entityNamespace);
    }

    /**
     * Get the total of following of the user.
     *
     * @return int
     */
    public function getTotalFollowers() : int
    {
        return Follow::getTotalFollowers($this);
    }

    /**
     * Verify if the user follow the tag.
     *
     * @deprecated version 4.0
     *
     * @return bool
     */
    public function isFollow() : bool
    {
        return (bool) UsersFollows::count([
            'conditions' => 'users_id = :userId: AND entity_id = :entityId: AND entity_namespace = :entityName: AND is_deleted = 0',
            'bind' => [
                'userId' => Di::getDefault()->get('userData')->getId(),
                'entityId' => $this->getId(),
                'entityName' => get_class($this)
            ]
        ]);
    }

    /**
     * Allow user o follow the entity.
     *
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public function follow(ModelInterface $entity) : bool
    {
        return Follow::follow($this, $entity);
    }

    /**
     * Un follow a entity.
     *
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public function unFollow(ModelInterface $entity) : bool
    {
        return Follow::unFollow($this, $entity);
    }

    /**
     * Is following the current entity.
     *
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public function isFollowing(ModelInterface $entity) : bool
    {
        return Follow::isFollowing($this, $entity);
    }
}
