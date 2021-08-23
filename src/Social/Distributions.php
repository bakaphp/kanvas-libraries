<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social;

use Baka\Queue\Queue;
use Kanvas\Packages\Social\Contracts\Follows\FollowableInterface;
use Kanvas\Packages\Social\Models\ChannelMessages;
use Kanvas\Packages\Social\Models\Channels;
use Kanvas\Packages\Social\Models\Messages;

class Distributions
{
    /**
     * Send Messages to channels feeds.
     *
     * @param string $channelName
     *
     * @return ChannelsModel
     */
    public static function sendToChannelFeed(Channels $channel, Messages $message) : ChannelMessages
    {
        $channelMessage = new ChannelMessages();
        $channelMessage->messages_id = $message->getId();
        $channelMessage->users_id = $message->users_id;
        $channelMessage->channel_id = $channel->getId();
        $channelMessage->saveOrFail();

        $channel->last_message_id = $message->getId();
        $channel->saveOrFail();

        return $channelMessage;
    }

    /**
     * Send the message to the users feeds that follow the entity.
     *
     * @param Messages $message
     * @param FollowableInterface $followableEntity
     *
     * @return void
     */
    public static function sendToUsersFeeds(Messages $message, FollowableInterface $followableEntity) : void
    {
        $messageFormatted = self::formatDistributionNewMessage($message, $followableEntity);

        Queue::setDurable(false);

        Queue::send('feedsDistribution', json_encode($messageFormatted));
    }

    /**
     * Format the data that will be send to the distribution queue when new message is created.
     *
     * @param Messages $message
     * @param FollowableInterface $followableEntity
     *
     * @return array
     */
    public static function formatDistributionNewMessage(Messages $message, FollowableInterface $followableEntity) : array
    {
        $data = [
            'action' => '',
            'entity_id' => $followableEntity->getId(),
            'users_id' => 0,
            'apps_id' => $message->apps_id,
            'companies_id' => $message->companies_id,
            'message_id' => $message->getId(),
            'num_messages' => 0,
            'is_deleted' => 0,
            'entity_namespace' => get_class($followableEntity),
            'delete_message' => 0
        ];
        return $data;
    }
}
