<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Services\TagService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TagService $tagService
    ) {
    }

    /**
     * Get tags for selection/autocomplete inputs.
     */
    public function options(Request $request)
    {
        $options = $this->tagService->getTagOptions(
            $request->input('search'),
            (int) $request->input('limit', 50)
        );

        return $this->successResponse($options, 'Tag options retrieved successfully');
    }
}
