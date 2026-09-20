<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminPageController extends Controller
{
    /**
     * Display all pages.
     */
    public function index(): JsonResponse
    {
        $pages = Page::latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar halaman berhasil diambil',
            'data' => $pages,
        ]);
    }

    /**
     * Store a new page.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'status' => ['required', 'in:draft,published'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('pages', 'public');
        }

        $page = Page::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'] ?? null,
            'image' => $imagePath,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil dibuat',
            'data' => $page,
        ], 201);
    }

    /**
     * Display a page.
     */
    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail halaman berhasil diambil',
            'data' => $page,
        ]);
    }

    /**
     * Update a page.
     */
    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($page->id),
            ],
            'content' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            if ($page->image) {
                Storage::disk('public')->delete($page->image);
            }

            $data['image'] = $request->file('image')
                ->store('pages', 'public');
        }

        $page->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil diperbarui',
            'data' => $page->fresh(),
        ]);
    }

    /**
     * Delete a page.
     */
    public function destroy(Page $page): JsonResponse
    {
        if ($page->image) {
            Storage::disk('public')->delete($page->image);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil dihapus',
        ]);
    }
}