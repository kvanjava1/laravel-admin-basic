<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\Media\StoreMediaRequest;
use App\Http\Requests\AdminDashboard\Media\UpdateMediaRequest;
use App\Http\Resources\MediaDetailResource;
use App\Http\Resources\MediaListResource;
use App\Models\Media;
use App\Services\MediaService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MediaService $mediaService
    ) {
    }

    /**
     * Display a paginated listing of media.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'title', 'alt_text', 'caption', 'description', 'category_id', 'tags']);
        $media = $this->mediaService->getPaginatedMedia((int) $request->input('per_page', 12), $filters);

        return $this->successResponse([
            'data' => MediaDetailResource::collection($media->getCollection()),
            'current_page' => $media->currentPage(),
            'last_page' => $media->lastPage(),
            'per_page' => $media->perPage(),
            'total' => $media->total(),
            'from' => $media->firstItem(),
            'to' => $media->lastItem(),
        ], 'Media retrieved successfully');
    }

    /**
     * Store a newly created media item.
     */
    public function store(StoreMediaRequest $request)
    {
        $media = $this->mediaService->create($request->validated());

        return $this->successResponse($media, 'Media created successfully', 201);
    }

    /**
     * Display the specified media item.
     */
    public function show(Media $media)
    {
        return $this->successResponse(
            new MediaDetailResource($media->load(['category', 'creator', 'tags'])),
            'Media retrieved successfully'
        );
    }

    /**
     * Update the specified media item.
     */
    public function update(UpdateMediaRequest $request, Media $media)
    {
        $updatedMedia = $this->mediaService->update($media, $request->validated());

        return $this->successResponse(
            new MediaDetailResource($updatedMedia),
            'Media updated successfully'
        );
    }

    /**
     * Soft delete the specified media item.
     */
    public function destroy(Media $media)
    {
        $this->mediaService->delete($media);

        return $this->successResponse(null, 'Media deleted successfully');
    }
}
