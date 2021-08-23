<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

use Baka\Support\Str;

class Channels extends BaseModel
{
    public $id;
    public string $name;
    public string $slug;
    public string $description;
    public string $entity_namespace;
    public string $entity_id;
    public ?int $last_message_id = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('channels');

        $this->hasManyToMany(
            'id',
            ChannelMessages::class,
            'channel_id',
            'messages_id',
            Messages::class,
            'id',
            [
                'alias' => 'messages',
                'reusable' => true
            ]
        );
    }

    /**
     * Before create.
     *
     * @return void
     */
    public function beforeCreate()
    {
        parent::beforeCreate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Get Channel by name.
     *
     * @param string $channelName
     *
     * @return self
     */
    public static function getByName(string $channelName) : self
    {
        return self::findFirstOrFail([
            'conditions' => 'name = :channelName: AND is_deleted = 0',
            'bind' => [
                'channelName' => $channelName
            ]
        ]);
    }
}
