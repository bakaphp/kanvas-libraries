<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Services;

use Gewaer\Models\UsersInteractions;
use Kanvas\Packages\Social\Contract\Users\UserInterface;
use Kanvas\Packages\Social\Models\MessageComments;
use Kanvas\Packages\Social\Models\Messages;

class Comments
{
    /**
     * Get a comment by its ID
     *
     * @param string $uuid
     * @return MessageComments
     */
    public static function get(string $uuid): MessageComments
    {
        $comment = MessageComments::getByIdOrFail($uuid);

        return $comment;
    }

    /**
     * Create a comment for a message
     *
     * @param string $messageId
     * @param string $message
     * @return MessageComments
     */
    public static function add(string $messageId, string $message): MessageComments
    {
        $messageData = Messages::getByIdOrFail($messageId);

        return $messageData->comment($message);
    }

    /**
     * Update a comment by its id
     *
     * @param string $commentId
     * @param string $message
     * @return MessageComments
     */
    public static function edit(string $commentId, string $message): MessageComments
    {
        $comment = MessageComments::getByIdOrFail($commentId);
        $comment->message = $message;
        $comment->updateOrFail();

        return $comment;
    }
    
    /**
     * Delete a comment
     *
     * @param string $commentId
     * @return boolean
     */
    public static function delete(string $commentId, UserInterface $user): bool
    {
        $comment = MessageComments::getByIdOrFail($commentId);
        $comment->deleteInteraction(UsersInteractions::COMMENT, $user);
        return (bool) $comment->softDelete();
    }

    /**
     * Reply a comment by its Id
     *
     * @param string $commentId
     * @param string $message
     * @return MessageComments
     */
    public static function reply(string $commentId, string $message): MessageComments
    {
        $comment = MessageComments::getByIdOrFail($commentId);
        
        return $comment->reply($message);
    }
}
