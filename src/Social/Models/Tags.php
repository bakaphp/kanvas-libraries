<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

use Kanvas\Packages\Social\Contracts\Follows\FollowableInterface;
use Kanvas\Packages\Social\Contracts\Follows\FollowersTrait;
use Kanvas\Packages\Social\Contracts\Interactions\EntityInteractionsTrait;

class Tags extends BaseModel implements FollowableInterface
{
    use FollowersTrait;
    use EntityInteractionsTrait;

    public $id;
    public int $apps_id;
    public int $companies_id;
    public int $users_id;
    public string $name;
    public string $slug;
    public int $weight = 0;
    public int $is_feature = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('tags');

        $this->hasManyToMany(
            'id',
            MessageTags::class,
            'tags_id',
            'message_id',
            Messages::class,
            'id',
            [
                'reusable' => true,
                'alias' => 'messages'
            ]
        );

        $this->hasMany(
            'id',
            UsersFollows::class,
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'follows',
                'params' => 'is_deleted = 0'
            ]
        );

        $this->hasOne(
            'id',
            UsersFollows::class,
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'follow',
                'params' => [
                    'conditions' => 'entity_namespace = :namespace:',
                    'bind' => [
                        'namespace' => get_class($this)
                    ]
                ]
            ]
        );

        $this->hasMany(
            'id',
            UsersInteractions::class,
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'interactions',
                'params' => [
                    'conditions' => 'entity_namespace = :namespace:',
                    'bind' => [
                        'namespace' => get_class($this)
                    ]
                ]
            ]
        );

        $this->hasOne(
            'id',
            UsersInteractions::class,
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'interaction',
                'params' => [
                    'conditions' => 'entity_namespace = :namespace:',
                    'bind' => [
                        'namespace' => get_class($this)
                    ]
                ]
            ]
        );
    }
}
