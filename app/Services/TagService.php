<?php

namespace App\Services;

use App\Repositories\TagRepository;

class TagService
{
    public function __construct(
        protected TagRepository $tagRepository
    ) {
    }

    /**
     * Get tag options for autocomplete/select inputs.
     */
    public function getTagOptions(?string $search = null, int $limit = 50)
    {
        return $this->tagRepository->getOptions($search, $limit);
    }
}
