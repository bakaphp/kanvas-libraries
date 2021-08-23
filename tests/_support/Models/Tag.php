<?php

namespace Kanvas\Packages\Test\Support\Models;

use Kanvas\Packages\Social\Contracts\Follows\FollowableInterface;
use Kanvas\Packages\Social\Contracts\Follows\FollowersTrait;
use Kanvas\Packages\Social\Contracts\Interactions\TotalInteractionsTrait;

class Tag extends BaseModel implements FollowableInterface
{
    use FollowersTrait;
    use TotalInteractionsTrait;

    public int $id = 1;

    public function getId() : int
    {
        return $this->id;
    }
}
