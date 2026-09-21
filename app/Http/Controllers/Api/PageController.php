<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $pages = Page::query()
            ->where('status', 'published')
            ->latest()
            ->get();

        return PageResource::collection($pages);
    }

    public function show(string $slug): PageResource
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return new PageResource($page);
    }
}