<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social;

use Baka\Contracts\Auth\UserInterface;
use Kanvas\Packages\Social\Models\Tags as TagsModel;
use Kanvas\Packages\Social\Models\UserMessages;
use Phalcon\Di;
use Phalcon\Utils\Slug;

class Tags
{
    /**
     * Get a Tag by its ID.
     *
     * @param string $uuid
     *
     * @return TagsModel
     */
    public static function get(string $uuid) : TagsModel
    {
        $tag = TagsModel::getByIdOrFail($uuid);

        return $tag;
    }

    /**
     * To be describe.
     *
     * @param UserMessages $feed
     * @param string $message
     *
     * @return void
     */
    public static function process(UserMessages $feed, string $message)
    {
    }

    /**
     * Create a new Tag.
     *
     * @param UserInterface $user
     * @param string $message
     *
     * @return TagsModel
     */
    public static function create(UserInterface $user, string $message) : TagsModel
    {
        $newTag = new TagsModel();
        $newTag->name = $message;
        $newTag->apps_id = $user->getDefaultCompany()->getId();
        $newTag->companies_id = Di::getDefault()->get('app')->getId();
        $newTag->users_id = $user->getId();
        $newTag->slug = Slug::generate($message);
        $newTag->saveOrFail();

        return $newTag;
    }

    /**
     * Update an existing Tag.
     *
     * @param TagsModel $tag
     * @param string $message
     *
     * @return TagsModel
     */
    public static function update(TagsModel $tag, string $message) : TagsModel
    {
        $tag->name = $message;
        $tag->slug = Slug::generate($message);
        $tag->updateOrFail();

        return $tag;
    }

    /**
     * Delete an existing Tag.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public static function delete(TagsModel $tag) : bool
    {
        return $tag->deleteOrFail();
    }

    /**
     * SoftDelete an existing tag.
     *
     * @param TagsModel $tag
     *
     * @return bool
     */
    public static function softDelete(TagsModel $tag) : bool
    {
        $tag->softDelete();

        return (bool)$tag->is_deleted;
    }
}
