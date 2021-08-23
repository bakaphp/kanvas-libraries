<?php
declare(strict_types=1);

namespace Kanvas\Packages\Social\Models;

use Kanvas\Packages\Social\Contracts\Interactions\EntityInteractionsTrait;

class Reactions extends BaseModel
{
    use EntityInteractionsTrait;

    public string $name;
    public int $apps_id;
    public int $companies_id;
    public ?string $icon = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->hasMany(
            'id',
            UsersReactions::class,
            'reactions_id',
            [
                'params' => 'is_deleted = 0',
                'reusable' => true,
                'alias' => 'usersReactions'
            ]
        );
    }
}
