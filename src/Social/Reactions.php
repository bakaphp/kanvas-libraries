<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social;

use Baka\Contracts\Auth\UserInterface;
use Exception;
use Kanvas\Packages\Social\Jobs\RemoveMessagesReactions;
use Kanvas\Packages\Social\Models\Reactions as ReactionsModel;
use Kanvas\Packages\Social\Models\UsersReactions;
use Kanvas\Packages\Social\Utils\StringFormatter;
use Phalcon\Di;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;

class Reactions
{
    /**
     * Add a new reaction to the current entity.
     *
     * @param string $reaction
     * @param UserInterface $user
     * @param ModelInterface $entity
     *
     * @return bool
     */
    public static function addMessageReaction(string $reaction, UserInterface $user, ModelInterface $entity) : bool
    {
        if (StringFormatter::isStringEmoji($reaction)) {
            $reactionData = self::getReactionByEmoji($reaction, $user);
        } else {
            $reactionData = self::getReactionByName($reaction, $user);
        }

        $userReaction = UsersReactions::findFirst([
            'conditions' => 'users_id = :userId: AND 
                                reactions_id = :reactionId: AND 
                                entity_namespace = :namespace: AND 
                                entity_id = :entityId:',
            'bind' => [
                'userId' => $user->getId(),
                'reactionId' => $reactionData->getId(),
                'namespace' => get_class($entity),
                'entityId' => $entity->getId(),
            ]
        ]);

        if ($userReaction) {
            self::removeUserReaction($userReaction, $user);
            return (bool) $userReaction->is_deleted;
        } elseif (!$userReaction) {
            $userReaction = new UsersReactions();
            $userReaction->users_id = $user->getId();
            $userReaction->reactions_id = $reactionData->getId();
            $userReaction->entity_namespace = get_class($entity);
            $userReaction->entity_id = $entity->getId();
            $userReaction->saveOrFail();
        }

        return (bool) $userReaction->is_deleted;
    }

    /**
     * Return an reaction object by its name.
     *
     * @param string $reactionName
     * @param UserInterface $user
     *
     * @return ReactionsModel
     */
    public static function getReactionByName(string $reactionName, UserInterface $user) : ReactionsModel
    {
        return ReactionsModel::findFirstOrFail([
            'conditions' => 'name = :reaction: AND apps_id = :appId: AND companies_id = :companyId: and is_deleted = 0',
            'bind' => [
                'reaction' => $reactionName,
                'companyId' => $user->getDefaultCompany()->getId(),
                'appId' => Di::getDefault()->get('app')->getId()
            ]
        ]);
    }

    /**
     * Return an reaction object by its icon.
     *
     * @param string $reactionEmoji
     * @param UserInterface $user
     *
     * @return ReactionsModel
     */
    public static function getReactionByEmoji(string $reactionEmoji, UserInterface $user) : ReactionsModel
    {
        return ReactionsModel::findFirstOrFail([
            'conditions' => 'icon = :emoji: AND apps_id = :appId: AND companies_id = :companyId: AND is_deleted = 0',
            'bind' => [
                'emoji' => $reactionEmoji,
                'companyId' => $user->getDefaultCompany()->getId(),
                'appId' => Di::getDefault()->get('app')->getId()
            ]
        ]);
    }

    /**
     * Create a new reaction with or without emoji, $reactionEmoji must be an unicode valid emoji.
     *
     * @param string $reactionName
     * @param UserInterface $user
     * @param string $reactionEmoji
     *
     * @return Reactions
     */
    public static function createReaction(string $reactionName, UserInterface $user, string $reactionEmoji = null) : ReactionsModel
    {
        if ($reactionEmoji && !StringFormatter::isStringEmoji($reactionEmoji)) {
            throw new Exception('Emoji must have a valid unicode format');
        }

        $reaction = new ReactionsModel();
        $reaction->name = $reactionName;
        $reaction->apps_id = Di::getDefault()->get('app')->getId();
        $reaction->companies_id = $user->getDefaultCompany()->getId();
        $reaction->icon = $reactionEmoji;
        $reaction->saveOrFail();

        return $reaction;
    }

    /**
     * Return the group of reactions that have emojis and bellow to the current app.
     *
     * @return Simple
     */
    public static function getReactionsEmojis() : Simple
    {
        return ReactionsModel::find([
            'conditions' => 'icon IS NOT NULL AND apps_id = :appId: AND is_deleted = 0',
            'bind' => [
                'appId' => Di::getDefault()->get('app')->getId()
            ]
        ]);
    }

    /**
     * Delete a Reaction by its id.
     *
     * @param ReactionsModel $reaction
     *
     * @return bool
     */
    public static function deleteReaction(ReactionsModel $reaction) : bool
    {
        return (bool) $reaction->softDelete();

        RemoveMessagesReactions::dispatch($reaction);
    }

    /**
     * Edit a reaction.
     *
     * @param ReactionsModel $reaction
     * @param string $name
     *
     * @return ReactionsModel
     */
    public static function editReaction(ReactionsModel $reaction, string $name) : ReactionsModel
    {
        $reaction->name = $name;
        $reaction->saveOrFail();

        return $reaction;
    }

    /**
     * Get the interaction made by the user to the current entity.
     *
     * @param int $interactionId
     * @param UserInterface $user
     *
     * @return UsersReactions|null
     */
    public static function getUserReactionByName(ModelInterface $entity, string $reactionName, UserInterface $user) : ?UsersReactions
    {
        return UsersReactions::findFirst([
            'conditions' => 'users_id = :userId: AND reactions_id = :reactionId: AND 
                            entity_namespace = :entityNamespace: AND is_deleted = 0',
            'bind' => [
                'userId' => $user->getId(),
                'reactionId' => self::getReactionByName($reactionName, $user)->getId(),
                'entityNamespace' => get_class($entity)
            ]
        ]);
    }

    /**
     * Remove or Restore a reaction based on its is_deleted.
     *
     * @param UsersReactions $reaction
     *
     * @return void
     */
    public static function removeUserReaction(UsersReactions $reaction) : void
    {
        if ($reaction->is_deleted) {
            $reaction->is_deleted = 0;
        } else {
            $reaction->is_deleted = 1;
        }
        $reaction->saveOrFail();
    }
}
