<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Comments;

use Canvas\Contracts\FileSystemModelTrait;
use Canvas\Models\Users;
use Kanvas\Packages\Social\Contracts\Interactions\EntityInteractionsTrait;
use Kanvas\Packages\Social\Models\BaseModel;

class Model extends BaseModel
{
    use EntityInteractionsTrait;
    use FileSystemModelTrait;

    public $id;
    public int $apps_id;
    public int $companies_id;
    public int $users_id = 0;
    public string $message = '';
    public int $reactions_count = 0;
    public int $parent_id = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo('users_id', Users::class, 'id', ['alias' => 'users', 'reusable' => true]);

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

        $this->hasMany(
            'id',
            UsersReactions::class,
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'reactions',
                'params' => [
                    'conditions' => 'entity_namespace = :namespace: AND is_deleted = 0',
                    'bind' => [
                        'namespace' => get_class($this)
                    ]
                ]
            ]
        );

        $this->hasOne(
            'id',
            UsersReactions::class,
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'reaction',
                'params' => [
                    'conditions' => 'entity_namespace = :namespace: AND is_deleted = 0',
                    'bind' => [
                        'namespace' => get_class($this)
                    ]
                ]
            ]
        );
    }

    /**
     * Return the id of the parent in case that comment is a reply.
     *
     * @return int
     */
    public function getParentId() : int
    {
        return $this->parent_id == 0 ? $this->getId() : $this->parent_id;
    }

    /**
     * Check if comment is parent.
     *
     * @return bool
     */
    public function isParent() : bool
    {
        return $this->parent_id == 0;
    }


    /**
     * After update.
     *
     * @return void
     */
    public function afterSave()
    {
        $this->associateFileSystem();
    }
}
