<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Services;

use Kanvas\Packages\Social\Contract\Messages\MessageableInterface;
use Kanvas\Packages\Social\Contract\Users\UserInterface;
use Kanvas\Packages\Social\Jobs\GenerateTags;
use Kanvas\Packages\Social\Jobs\RemoveMessagesFeed;
use Kanvas\Packages\Social\Models\AppModuleMessage;
use Kanvas\Packages\Social\Models\Messages as MessagesModel;
use Kanvas\Packages\Social\Models\UserMessages;
use Phalcon\Di;
use Phalcon\Mvc\Model\Resultset\Simple;

class Messages
{

    /**
     * Return a Message object by its id
     *
     * @param string $uuid
     * @return MessagesModel
     */
    public static function getMessage(string $uuid): MessagesModel
    {
        $message = MessagesModel::getByIdOrFail($uuid);
        
        return $message;
    }

    /**
     * To be describe
     *
     * @param UserInterface $user
     * @param string $verb
     * @param array $message
     * @param array $object contains the entity object + its id.
     * @param string $distribution
     * @return UserMessages
     */
    public static function create(UserInterface $user, string $verb, array $message = [], ?MessageableInterface $object = null): MessagesModel
    {
        $newMessage = new MessagesModel();
        $newMessage->apps_id = Di::getDefault()->get('app')->getId();
        $newMessage->companies_id = $user->getDefaultCompany()->getId();
        $newMessage->users_id = (int) $user->getId();
        $newMessage->message_types_id = MessageTypes::getTypeByVerb($verb)->getId();
        $newMessage->message = json_encode($message);
        $newMessage->saveOrFail();

        GenerateTags::dispatch($user, $newMessage);

        $newAppModule = new AppModuleMessage();
        $newAppModule->message_id = $newMessage->getId();
        $newAppModule->message_types_id = $newMessage->message_types_id;
        $newAppModule->apps_id = $newMessage->apps_id; //Duplicate data?
        $newAppModule->companies_id = $newMessage->companies_id; //Duplicate data?
        $newAppModule->system_modules =  $object ? get_class($object) : null;
        $newAppModule->entity_id =  $object ? $object->getId() : null;
        $newAppModule->saveOrFail();

        return $newMessage;
    }

    /**
     * To be describe
     *
     * @param string $uuid
     * @param array $message
     * @return void
     */
    public static function update(string $uuid, array $message)
    {
    }

    /**
     * Delete the message and remove it from the users feeds
     *
     * @param string $uuid
     * @return bool
     */
    public static function delete(string $uuid): bool
    {
        $message = MessagesModel::getByIdOrFail($uuid);

        RemoveMessagesFeed::dispatch($message);

        return $message->softDelete();
    }
}