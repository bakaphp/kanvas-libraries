<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Elasticsearch\ElasticIndexModelTrait;
use function Baka\isJson;
use Canvas\Contracts\CustomFields\CustomFieldsTrait;
use Canvas\Contracts\FileSystemModelTrait;
use Canvas\Models\Behaviors\Uuid;
use Canvas\Models\SystemModules;
use Canvas\Models\Users;
use Kanvas\Packages\Social\Contracts\Interactions\CustomTotalInteractionsTrait;
use Kanvas\Packages\Social\Contracts\Interactions\InteractionsTrait;
use Kanvas\Packages\Social\Contracts\Messages\MessageableEntityInterface;
use Kanvas\Packages\Social\Contracts\Messages\MessagesInterface;
use Kanvas\Packages\Social\Jobs\ElasticMessages;
use Phalcon\Di;

class Messages extends BaseModel implements MessagesInterface, MessageableEntityInterface
{
    use CustomTotalInteractionsTrait;
    use InteractionsTrait;
    use FileSystemModelTrait;
    use ElasticIndexModelTrait;
    use CustomFieldsTrait;

    use ElasticIndexModelTrait, CustomFieldsTrait{
        CustomFieldsTrait::afterDelete insteadof ElasticIndexModelTrait;
    }

    public int $parent_id = 0;
    public ?string $parent_unique_id = null;
    public ?string $uuid = null;
    public int $apps_id = 0;
    public int $companies_id = 0;
    public int $users_id = 0;
    public int $message_types_id = 0;
    public ?string $message = null;
    public ?int $reactions_count = 0;
    public ?int $comments_count = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setElasticRawData();

        $this->setSource('messages');
        $this->belongsTo('users_id', Users::class, 'id', ['alias' => 'users']);
        $this->belongsTo('parent_id', self::class, 'id', ['alias' => 'parentMessage']);

        $this->addBehavior(
            new Uuid()
        );

        $this->hasMany(
            'id',
            self::class,
            'parent_id',
            [
                'alias' => 'relatedMessages'
            ]
        );

        $this->hasOne(
            'id',
            AppModuleMessage::class,
            'message_id',
            [
                'reusable' => true,
                'alias' => 'appModuleMessage'
            ]
        );

        $this->hasMany(
            'id',
            MessageVariables::class,
            'message_id',
            [
                'reusable' => true,
                'alias' => 'messageVariables'
            ]
        );

        $this->hasMany(
            'id',
            MessageComments::class,
            'message_id',
            [
                'reusable' => true,
                'alias' => 'comments'
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
                    'conditions' => 'entity_namespace = :namespace: AND is_deleted = 0',
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
                    'conditions' => 'entity_id = :entityId: AND entity_namespace = :namespace:',
                    'bind' => [
                        'namespace' => get_class($this),
                        'entityId' => $this->id
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

        $this->hasOne(
            'message_types_id',
            MessageTypes::class,
            'id',
            [
                'reusable' => true,
                'alias' => 'message_type'
            ]
        );

        $this->hasManyToMany(
            'id',
            MessageTags::class,
            'message_id',
            'tags_id',
            Tags::class,
            'id',
            [
                'reusable' => true,
                'alias' => 'tags'
            ]
        );

        $this->hasManyToMany(
            'id',
            ChannelMessages::class,
            'messages_id',
            'channel_id',
            Channels::class,
            'id',
            [
                'alias' => 'channels'
            ]
        );

        $systemModule = SystemModules::getByModelName(self::class, false);
        $this->hasMany(
            'id',
            'Canvas\Models\FileSystemEntities',
            'entity_id',
            [
                'reusable' => true,
                'alias' => 'files',
                'params' => [
                    'conditions' => 'system_modules_id = ?0',
                    'bind' => [$systemModule->getId()]
                ]
            ]
        );
    }

    /**
     * Create a comment for a message.
     *
     * @param string $messageId
     * @param string $message
     *
     * @return MessageComments
     */
    public function comment(string $message, UserInterface $user) : MessageComments
    {
        $comment = new MessageComments();
        $comment->message_id = $this->getId();
        $comment->apps_id = Di::getDefault()->get('app')->getId();
        $comment->companies_id = $user->getDefaultCompany()->getId();
        $comment->users_id = $user->getId();
        $comment->message = $message;
        $comment->saveOrFail();

        return $comment;
    }

    /**
     * Attach a System Module to this message.
     *
     * @param MessageableEntityInterface $entity
     *
     * @return AppModuleMessage
     */
    public function addSystemModules(MessageableEntityInterface $entity) : AppModuleMessage
    {
        $newAppModule = new AppModuleMessage();
        $newAppModule->message_id = $this->getId();
        $newAppModule->message_types_id = $this->message_types_id;
        $newAppModule->apps_id = $this->apps_id; //Duplicate data?
        $newAppModule->companies_id = $this->companies_id; //Duplicate data?
        $newAppModule->system_modules = get_class($entity);
        $newAppModule->entity_id = $entity->getId();
        $newAppModule->saveOrFail();

        return $newAppModule;
    }

    /**
     * Verify if the $userId owns this message.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function hasUser(int $userId) : bool
    {
        return $userId == $this->users_id;
    }

    /**
     * Verify if message has a parent message.
     *
     * @return bool
     */
    public function hasParent() : bool
    {
        return $this->parent_id > 0;
    }

    /**
     * Set parent for this msg.
     *
     * @param self $parent
     * @param string $uniqueId
     *
     * @return void
     */
    public function setParent(MessagesInterface $parent, string $uniqueId) : void
    {
        $this->parent_id = $parent->getId();
        $this->parent_unique_id = $uniqueId;

        //update the parent so the msg update on the feeds list
        $parent->updated_at = date('Y-m-H H:i:s');
        $parent->updateOrFail();
    }

    /**
     * Get message as Array.
     *
     * @return array
     */
    public function getMessage() : array
    {
        if (isJson($this->message)) {
            return json_decode($this->message, true);
        }

        return [];
    }

    /**
     * Set a key value the message array.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setMessage(string $key, $value) : void
    {
        $message = $this->getMessage();
        $message[$key] = $value;

        $this->message = json_encode($message);
    }

    /**
     * Upload Files.
     *
     * @todo move this to the baka class
     *
     * @return void
     */
    public function afterSave()
    {
        $this->associateFileSystem();
        $this->clearFileSystemCache();
        ElasticMessages::dispatch($this);

        if ($this->hasParent()) {
            ElasticMessages::dispatch($this->getParentMessage());
        }
    }

    /**
     * Is this msg indexable in elastic.
     *
     * @return bool
     */
    public function isIndexable() : bool
    {
        return !$this->is_deleted;
    }
}
