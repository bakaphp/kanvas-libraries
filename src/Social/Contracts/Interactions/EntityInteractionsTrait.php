<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Contracts\Interactions;

use Kanvas\Packages\Social\Interactions;

trait EntityInteractionsTrait
{

    /**
     * For the given entity get the total interactions.
     *
     * @param string $interactionNam
     *
     * @return int
     */
    public function getTotalInteractions(string $interactionNam) : int
    {
        return Interactions::getTotalByEntity($this, $interactionNam);
    }
}
