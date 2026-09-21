<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $announcements = Announcement::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(10);

        return AnnouncementResource::collection($announcements);
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        if ($announcement->status !== 'published') {
            abort(404);
        }

        return new AnnouncementResource($announcement);
    }
}