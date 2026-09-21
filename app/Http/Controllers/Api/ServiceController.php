<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        return ServiceResource::collection($services);
    }

    public function show(string $slug): ServiceResource
    {
        $service = Service::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return new ServiceResource($service);
    }
}