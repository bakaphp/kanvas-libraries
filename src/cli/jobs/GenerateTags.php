<?php

namespace Kanvas\Packages\Jobs;

use Kanvas\Packages\Social\Models\Messages;
use Kanvas\Packages\Social\Models\MessageTags;
use Kanvas\Packages\Social\Models\Tags;
use Kanvas\Packages\Social\Models\UsersInteractions;
use Kanvas\Packages\Social\Utils\StringFormatter;
use Phalcon\Di;
use Phalcon\Utils\Slug;

class GenerateTags
{
    protected $user;
    protected $message;

    /**
     * Construct
     *
     * @param UsersInteractions $user
     * @param Messages $message
     */
    public function __construct(UsersInteractions $user, Messages $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Handle the Generate the tags of the message.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $tags = StringFormatter::getHashtagToString($this->message->message);
        foreach ($tags as $tag) {
            $tagData = Tags::findFirstOrCreate(
                [
                    'conditions' => 'slug = :tag_slug: AND is_deleted = 0',
                    'bind' => [
                        'tag_slug' => Slug::generate($tag)
                    ]
                ],
                [
                    'name' => $tag,
                    'slug' => Slug::generate($tag),
                    'apps_id' => $this->user->getDefaultCompany()->getId(),
                    'companies_id' => Di::getDefault()->get('app')->getId(),
                ]
            );
            $messageTag = new MessageTags();
            $messageTag->message_id = $this->message->getId();
            $messageTag->tags_id = $tagData->getId();
            $messageTag->save();
        }

        Di::getDefault()->get('log')->info('Generate tags for message ' . $this->message->getId());

        return true;
    }
}