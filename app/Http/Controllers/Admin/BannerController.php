<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Admin - Banners",
 *     description="Admin API Endpoints for banner management"
 * )
 */
class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Banner::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort_by field
        $allowedSortFields = ['id', 'title_ar', 'title_en', 'status', 'order', 'is_featured', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort_order
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
        
        // Apply sorting
        if ($sortBy === 'order') {
            // If sorting by order, use order as primary sort, then created_at
            $query->orderBy('order', $sortOrder)
                  ->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $banners = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Banners retrieved successfully',
            'data' => BannerResource::collection($banners),
            'meta' => [
                'current_page' => $banners->currentPage(),
                'per_page' => $banners->perPage(),
                'total' => $banners->total(),
                'last_page' => $banners->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable', // يمكن أن يكون ملف أو path (string)
            'image_path' => 'nullable|string', // path من /banners/upload/image
            'link' => 'nullable|url|max:500',
            'status' => 'sometimes|in:active,inactive',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        // التحقق من الصورة: إما ملف جديد أو path من الرفع المسبق
        if ($request->hasFile('image')) {
            // رفع ملف جديد
            $validated['image'] = $request->file('image')->store('banners', 'public');
        } elseif ($request->has('image_path') && !empty($request->image_path)) {
            // استخدام path من الرفع المسبق - التحقق من وجود الملف
            if (!Storage::disk('public')->exists($request->image_path)) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'image_path' => ['The specified image path does not exist.']
                    ]
                ], 422);
            }
            $validated['image'] = $request->image_path;
        } else {
            // لا توجد صورة
            $validated['image'] = null;
        }

        // إزالة image_path من validated لأنه ليس حقل في الجدول
        unset($validated['image_path']);

        $banner = Banner::create($validated);

        return response()->json([
            'message' => 'Banner created successfully',
            'data' => new BannerResource($banner),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);

        return response()->json([
            'message' => 'Banner retrieved successfully',
            'data' => new BannerResource($banner),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title_ar' => 'sometimes|string|max:255',
            'title_en' => 'sometimes|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable', // يمكن أن يكون ملف أو path (string)
            'image_path' => 'nullable|string', // path من /banners/upload/image
            'link' => 'nullable|url|max:500',
            'status' => 'sometimes|in:active,inactive',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        // التحقق من الصورة: إما ملف جديد أو path من الرفع المسبق
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            // رفع ملف جديد
            $validated['image'] = $request->file('image')->store('banners', 'public');
        } elseif ($request->has('image_path') && !empty($request->image_path)) {
            // استخدام path من الرفع المسبق - التحقق من وجود الملف
            if (!Storage::disk('public')->exists($request->image_path)) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'image_path' => ['The specified image path does not exist.']
                    ]
                ], 422);
            }
            // Delete old image if exists and different from new one
            if ($banner->image && $banner->image !== $request->image_path && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->image_path;
        } elseif ($request->has('image') && $request->image === null) {
            // حذف الصورة (إرسال null صريح)
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = null;
        } else {
            // لا يوجد تحديث للصورة، إزالة image من validated
            unset($validated['image']);
        }

        // إزالة image_path من validated لأنه ليس حقل في الجدول
        unset($validated['image_path']);

        $banner->update($validated);

        return response()->json([
            'message' => 'Banner updated successfully',
            'data' => new BannerResource($banner),
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        return response()->json([
            'message' => 'Image uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        
        // Delete image if exists
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();

        return response()->json([
            'message' => 'Banner deleted successfully',
        ]);
    }
}
