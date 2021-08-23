<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

class Interactions extends BaseModel
{
    public ?string $name = null;
    public ?string $title = null;
    public ?string $icon = null;

    const REACT = 1;
    const SAVE = 2;
    const COMMENT = 3;
    const REPLIED = 4;
    const FOLLOWING = 5;
    const FOLLOWERS = 6;
    const MESSAGE = 7;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('interactions');

        $this->hasMany(
            'id',
            UsersInteractions::class,
            'interactions_id',
            [
                'alias' => 'usersInteractions',
                'reusable' => true,
            ]
        );
    }

    /**
     * Get the interaction by name.
     *
     * @param string $interaction
     *
     * @return self
     */
    public static function getByName(string $name) : self
    {
        $name = strtolower(trim($name));

        return self::findFirstOrCreate([
            'conditions' => 'name = :name: and is_deleted = 0',
            'bind' => [
                'name' => $name,
            ],

        ], [
            'name' => $name,
            'title' => $name,
        ]);
    }

    /**
     * Verify if the interaction is a comment/reply.
     *
     * @param int $interactionId
     *
     * @return bool
     */
    public static function isComment(int $interactionId) : bool
    {
        return $interactionId == self::COMMENT || $interactionId == self::REPLIED;
    }
}
