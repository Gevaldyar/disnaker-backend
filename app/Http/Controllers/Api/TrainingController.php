<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingResource;
use App\Models\Training;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrainingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $trainings = Training::query()
            ->where('status', 'published')
            ->latest('start_date')
            ->paginate(10);

        return TrainingResource::collection($trainings);
    }

    public function show(Training $training): TrainingResource
    {
        if ($training->status !== 'published') {
            abort(404);
        }

        return new TrainingResource($training);
    }
}