<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Contracts\Comments;

interface Comments
{
    /**
     * Return the id of the parent in case that comment is a reply.
     *
     * @return int
     */
    public function getParentId() : int;

    /**
     * Check if comment is parent.
     *
     * @return bool
     */
    public function isParent() : boo;
}
