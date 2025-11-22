<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentRegistrationCardResource;
use App\Models\StudentRegistrationCard;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Public - Student Registration Card",
 *     description="Card content that promotes student registration in the Student Welfare Fund app"
 * )
 */
class StudentRegistrationCardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/student-registration-card",
     *     summary="Get the latest active student registration card",
     *     tags={"Public - Student Registration Card"},
     *     @OA\Response(
     *         response=200,
     *         description="Card retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student registration card retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRegistrationCardResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Card not configured",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student registration card not found"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        $card = StudentRegistrationCard::active()
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$card) {
            return response()->json([
                'message' => 'Student registration card not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Student registration card retrieved successfully',
            'data' => new StudentRegistrationCardResource($card),
        ]);
    }
}

