<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $news = News::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(10);

        return NewsResource::collection($news);
    }

    public function show(News $news): NewsResource
    {
        if ($news->status !== 'published') {
            abort(404);
        }

        return new NewsResource($news);
    }
}