<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Contracts\Interactions;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Database\ModelInterface;
use Kanvas\Packages\Social\Enums\Interactions as EnumsInteractions;
use Kanvas\Packages\Social\Interactions as SocialInteractions;

trait UsersInteractionsTrait
{
    /**
     * Interact with the object based on the action.
     *
     * @param string $action
     * @param Model $data
     *
     * @return void
     */
    public function interact(ModelInterface $entity, string $interactionName) : bool
    {
        return SocialInteractions::add($this, $entity, $interactionName);
    }

    /**
     * Like Entity.
     *
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public function likes(ModelInterface $entity) : bool
    {
        return self::interact($entity, EnumsInteractions::LIKE);
    }

    /**
     * Share entity.
     *
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public function share(ModelInterface $entity) : bool
    {
        return self::interact($entity, EnumsInteractions::SHARE);
    }

    /**
     * save to user list entity.
     *
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public function addToList(ModelInterface $entity) : bool
    {
        return self::interact($entity, EnumsInteractions::SAVE);
    }

    /**
     * User has interaction.
     *
     * @param ModelInterface $entity
     * @param string $interactionName
     *
     * @return bool
     */
    public function hasInteracted(ModelInterface $entity, string $interactionName) : bool
    {
        return SocialInteractions::has($this, $entity, $interactionName);
    }

    /**
     * Remove interaction.
     *
     * @param int $interactionId
     * @param UserInterface $user
     *
     * @return void
     */
    public function removeInteraction(ModelInterface $entity, string $interactionName) : bool
    {
        return SocialInteractions::remove($this, $entity, $interactionName);
    }

    /**
     * Get the total # of interactions of a given entity and interaction type.
     *
     * @param string $entity
     * @param string $interactionName
     *
     * @return bool
     */
    public function getTotalInteractions(string $entityNamespace, string $interactionName) : int
    {
        return SocialInteractions::getTotalByUser($this, $entityNamespace, $interactionName);
    }

    /**
     * Get total.
     *
     * @return int
     */
    public function getTotalReactions(string $entityNamespace) : int
    {
        return SocialInteractions::getTotalByUser($this, $entityNamespace, $interactionName);
    }

    /**
     * Get total.
     *
     * @return int
     */
    public function getTotalComments(string $entityNamespace) : int
    {
        return SocialInteractions::getTotalByUser($this, $entityNamespace, EnumsInteractions::COMMENT);
    }

    /**
     * Get total.
     *
     * @return int
     */
    public function getTotalReplies(string $entityNamespace) : int
    {
        return SocialInteractions::getTotalByUser($this, $entityNamespace, EnumsInteractions::REPLY);
    }

    /**
     * Get the total of following of the user.
     *
     * @return int
     */
    public function getTotalMessages(string $entityNamespace) : int
    {
        return SocialInteractions::getTotalByUser($this, $entityNamespace, EnumsInteractions::MESSAGE);
    }
}
