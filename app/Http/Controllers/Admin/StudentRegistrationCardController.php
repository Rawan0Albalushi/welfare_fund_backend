<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentRegistrationCardResource;
use App\Models\StudentRegistrationCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Admin - Student Registration Card",
 *     description="Manage the hero card that invites students to register for the Student Welfare Fund"
 * )
 */
class StudentRegistrationCardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/student-registration-card",
     *     summary="Get the configured student registration card",
     *     tags={"Admin - Student Registration Card"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Card retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student registration card retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRegistrationCardResource")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        $card = $this->resolveCard();

        return response()->json([
            'message' => 'Student registration card retrieved successfully',
            'data' => new StudentRegistrationCardResource($card),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/student-registration-card",
     *     summary="Update the student registration card",
     *     tags={"Admin - Student Registration Card"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(ref="#/components/schemas/StudentRegistrationCardResource")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Card updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student registration card updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRegistrationCardResource")
     *         )
     *     )
     * )
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'headline_ar' => 'sometimes|required|string|max:255',
            'headline_en' => 'sometimes|required|string|max:255',
            'subtitle_ar' => 'nullable|string',
            'subtitle_en' => 'nullable|string',
            'background' => 'nullable|array',
            'background.type' => 'nullable|in:gradient,solid,image',
            'background.color_from' => 'nullable|string|max:9',
            'background.color_to' => 'nullable|string|max:9',
            'background.color' => 'nullable|string|max:9',
            'background.gradient_direction' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
            'background_image' => 'nullable',
            'background_image_path' => 'nullable|string',
        ]);

        $card = $this->resolveCard();

        if (array_key_exists('background', $validated) && $validated['background'] === null) {
            $validated['background'] = [];
        }

        if ($request->hasFile('background_image')) {
            if ($card->background_image && Storage::disk('public')->exists($card->background_image)) {
                Storage::disk('public')->delete($card->background_image);
            }
            $validated['background_image'] = $request->file('background_image')->store('student-registration-cards', 'public');
        } elseif (!empty($request->input('background_image_path'))) {
            $path = $request->input('background_image_path');
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'background_image_path' => ['The specified image path does not exist.'],
                    ],
                ], 422);
            }

            if ($card->background_image && $card->background_image !== $path && Storage::disk('public')->exists($card->background_image)) {
                Storage::disk('public')->delete($card->background_image);
            }

            $validated['background_image'] = $path;
        } elseif ($request->has('background_image') && $request->input('background_image') === null) {
            if ($card->background_image && Storage::disk('public')->exists($card->background_image)) {
                Storage::disk('public')->delete($card->background_image);
            }
            $validated['background_image'] = null;
        } else {
            unset($validated['background_image']);
        }

        unset($validated['background_image_path']);

        $card->update($validated);

        return response()->json([
            'message' => 'Student registration card updated successfully',
            'data' => new StudentRegistrationCardResource($card),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/student-registration-card/upload-background",
     *     summary="Upload an image to use as the card background",
     *     tags={"Admin - Student Registration Card"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image uploaded successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="url", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function uploadBackgroundImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $validated['image']->store('student-registration-cards', 'public');

        return response()->json([
            'message' => 'Background image uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ],
        ], 201);
    }

    private function resolveCard(): StudentRegistrationCard
    {
        return StudentRegistrationCard::firstOrCreate(
            [],
            StudentRegistrationCard::defaultPayload()
        );
    }
}

