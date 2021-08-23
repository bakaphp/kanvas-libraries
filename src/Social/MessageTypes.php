<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social;

use Baka\Contracts\Auth\UserInterface;
use Kanvas\Packages\Social\Models\MessageTypes as MessageTypesModel;
use Phalcon\Di;

class MessageTypes
{
    /**
     * Create a new MessageTypes.
     *
     * @param UserInterface $user
     * @param string $verb
     * @param string $name
     * @param string $template
     * @param string $templatesPlural
     *
     * @return MessageTypesModel
     */
    public static function create(UserInterface $user, string $verb, string $name, string $template = '', string $templatesPlural = '') : MessageTypesModel
    {
        $messageType = new MessageTypesModel();
        $messageType->apps_id = Di::getDefault()->get('app')->getId();
        $messageType->languages_id = $user->getDefaultCompany()->language;
        $messageType->name = $name;
        $messageType->verb = $verb;
        $messageType->template = $template;
        $messageType->templatesPlural = $templatesPlural;
        $messageType->saveOrFail();

        return $messageType;
    }

    /**
     * Delete an existing message type.
     *
     * @param MessageTypesModel $messageType
     *
     * @return bool
     */
    public static function delete(MessageTypesModel $messageType) : bool
    {
        return (bool) $messageType->softDelete();
    }
}
