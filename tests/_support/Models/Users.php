<?php

namespace Kanvas\Packages\Test\Support\Models;

use Canvas\Models\Users as ModelsUsers;
use Kanvas\Packages\Social\Contracts\Follows\FollowableInterface;
use Kanvas\Packages\Social\Contracts\Follows\FollowersTrait;
use Kanvas\Packages\Social\Contracts\Interactions\UsersInteractionsTrait;

class Users extends ModelsUsers implements FollowableInterface
{
    use FollowersTrait;
    use UsersInteractionsTrait;
}
