<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social;

use Baka\Contracts\Auth\UserInterface;
use Kanvas\Packages\Social\Contracts\Messages\MessageableEntityInterface;
use Kanvas\Packages\Social\Contracts\Messages\MessagesInterface;
use Kanvas\Packages\Social\Jobs\GenerateTags;
use Kanvas\Packages\Social\Jobs\RemoveMessagesFeed;
use Kanvas\Packages\Social\Models\AppModuleMessage;
use Kanvas\Packages\Social\Models\ChannelMessages;
use Kanvas\Packages\Social\Models\Channels;
use Kanvas\Packages\Social\Models\Messages as MessagesModel;
use Kanvas\Packages\Social\Models\MessageTypes as MessageTypesModel;
use Kanvas\Packages\Social\Models\UserMessages;
use Phalcon\Di;
use Phalcon\Mvc\Model\Resultset\Simple;

class Messages
{
    /**
     * Return a Message object by its id.
     *
     * @param string $id
     *
     * @return MessagesModel
     */
    public static function getMessage(string $id) : MessagesInterface
    {
        return MessagesModel::getByIdOrFail($id);
    }

    /**
     * Return a Message object by its uuid.
     *
     * @param string $uuid
     *
     * @return MessagesModel
     */
    public static function getMessageByUuid(string $uuid) : MessagesInterface
    {
        return MessagesModel::findFirstOrFail([
            'conditions' => 'uuid = :uuid: AND is_deleted = 0',
            'bind' => ['uuid' => $uuid]
        ]);
    }

    /**
     * Get all the messages of a user.
     *
     * @param UserInterface $user
     * @param int $limit
     * @param int $page
     *
     * @return Simple
     */
    public static function getByUser(UserInterface $user, int $page = 1, int $limit = 25) : Simple
    {
        $feed = new UserMessages();
        return $feed->getUserFeeds($user, $limit, $page);
    }

    /**
     * Get all the messages of a channel.
     *
     * @param Channels $user
     * @param array $filter
     *
     * @deprecated
     *
     * @return Simple
     */
    public static function getByChannel(Channels $channel, int $page = 1, int $limit = 25, string $orderBy = 'id', string $sort = 'DESC', ?string $messageTypeId = null) : Simple
    {
        $feed = new ChannelMessages();
        return $feed->getMessagesByChannel($channel, $page, $limit, $orderBy, $sort, $messageTypeId);
    }

    /**
     * Create a new Msg.
     *
     * @param UserInterface $user
     * @param string $verb
     * @param array $message
     * @param array $object contains the entity object + its id.
     * @param string $distribution
     *
     * @return UserMessages
     */
    public static function create(UserInterface $user, string $verb, array $message = [], ?MessageableEntityInterface $object = null, bool $sendToUserFeeds = true) : MessagesInterface
    {
        $newMessage = new MessagesModel();
        $newMessage->apps_id = Di::getDefault()->get('app')->getId();
        $newMessage->companies_id = $user->getDefaultCompany()->getId();
        $newMessage->users_id = (int) $user->getId();
        $newMessage->message_types_id = MessageTypesModel::getTypeByVerb($verb)->getId();
        $newMessage->message = json_encode($message);
        $newMessage->created_at = date('Y-m-d H:i:s');
        $newMessage->saveOrFail();

        if ($object) {
            $newMessage->addSystemModules($object);
        }

        if ($sendToUserFeeds) {
            Distributions::sendToUsersFeeds($newMessage, $user);
        }

        GenerateTags::dispatch($user, $newMessage);

        return $newMessage;
    }

    /**
     * Create a new msg from a Object.
     *
     * @param UserInterface $user
     * @param string $verb
     * @param array $message
     * @param array $object contains the entity object + its id.
     * @param string $distribution
     *
     * @return UserMessages
     */
    public static function createByObject(UserInterface $user, string $verb, MessagesInterface $newMessage, MessageableEntityInterface $object, bool $sendToUserFeeds = true) : MessagesInterface
    {
        $newMessage->apps_id = Di::getDefault()->get('app')->getId();
        $newMessage->companies_id = $user->getDefaultCompany()->getId();
        $newMessage->users_id = (int) $user->getId();
        $newMessage->message_types_id = MessageTypesModel::getTypeByVerb($verb)->getId();
        $newMessage->created_at = date('Y-m-d H:i:s');
        $newMessage->saveOrFail();

        $newMessage->addSystemModules($object);

        if ($sendToUserFeeds) {
            Distributions::sendToUsersFeeds($newMessage, $user);
        }

        GenerateTags::dispatch($user, $newMessage);

        return $newMessage;
    }

    /**
     * To be describe.
     *
     * @param string $uuid
     * @param array $message
     *
     * @return void
     */
    public static function update(string $uuid, array $message)
    {
    }

    /**
     * Delete the message and remove it from the users feeds.
     *
     * @param string $uuid
     *
     * @return bool
     */
    public static function delete(string $uuid) : bool
    {
        $message = MessagesModel::getByIdOrFail($uuid);

        RemoveMessagesFeed::dispatch($message);

        return (bool) $message->softDelete();
    }

    /**
     * Get the message from an MessagesInterface if exist.
     *
     * @param MessagesInterface $object
     *
     * @return MessagesModel
     */
    public static function getMessageFrom(MessagesInterface $object) : MessagesModel
    {
        $module = AppModuleMessage::findFirstOrFail([
            'conditions' => 'system_modules = :objectNamespace: AND entity_id = :entityId: AND
                            apps_id = :appId: AND is_deleted = 0',
            'bind' => [
                'objectNamespace' => get_class($object),
                'entityId' => $object->getId(),
                'appId' => Di::getDefault()->get('app')->getId(),
            ]
        ]);

        return $module->getMessage();
    }

    /**
     * Return the App Module Message data from a message.
     *
     * @param MessagesModel $message
     *
     * @return AppModuleMessage
     */
    public static function getAppModuleMessageFromMessage(MessagesModel $message) : AppModuleMessage
    {
        return $message->getAppModuleMessage([
            'conditions' => 'is_deleted = 0'
        ]);
    }
}
