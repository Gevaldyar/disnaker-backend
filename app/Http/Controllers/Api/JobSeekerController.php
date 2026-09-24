<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\JobSeekerListResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerPublicResource;
use App\Models\JobSeekerProfile;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobSeekerController extends Controller
{
    /**
     * Menampilkan daftar pencari kerja yang profilnya publik.
     */
    public function index(): AnonymousResourceCollection
    {
        $query = JobSeekerProfile::query()
            ->where('is_public', true)
            ->with('skills');

        /*
         * Search:
         * nama, headline, atau bio.
         */
        if (request()->filled('search')) {
            $search = request('search');

            $query->where(function ($query) use ($search) {
                $query->where(
                    'full_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'headline',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'bio',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
         * Filter kota.
         */
        if (request()->filled('city')) {
            $query->where(
                'city',
                'like',
                '%' . request('city') . '%'
            );
        }

        /*
         * Filter berdasarkan skill.
         */
        if (request()->filled('skill')) {
            $skill = request('skill');

            $query->whereHas('skills', function ($query) use ($skill) {
                $query->where(
                    'skill_name',
                    'like',
                    "%{$skill}%"
                );
            });
        }

        $profiles = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return JobSeekerListResource::collection($profiles);
    }

    /**
     * Menampilkan detail profil publik.
     */
    public function show(JobSeekerProfile $jobSeeker): JobSeekerPublicResource
    {
        abort_unless(
            $jobSeeker->is_public,
            404
        );

        $jobSeeker->load([
            'skills',
            'educations',
            'experiences',
        ]);

        return new JobSeekerPublicResource($jobSeeker);
    }
}