<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User Donations",
 *     description="API Endpoints for user's donation history"
 * )
 */
class DonationsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/me/donations",
     *     summary="Get current user's donations",
     *     tags={"User Donations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Items per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (alias for limit, max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "failed", "expired"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"quick", "gift"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User donations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donations retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DonationResource")),
     *             @OA\Property(property="stats", type="object",
     *                 @OA\Property(property="total_donations", type="integer"),
     *                 @OA\Property(property="total_amount", type="number"),
     *                 @OA\Property(property="paid_donations", type="integer"),
     *                 @OA\Property(property="pending_donations", type="integer")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="showing_all", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // إنشاء query واحد يجمع جميع التبرعات
        $query = Donation::where(function ($q) use ($user) {
            // التبرعات المرتبطة مباشرة بالمستخدم
            $q->where('user_id', $user->id)
              // التبرعات المرتبطة برقم الهاتف (للتبرعات القديمة)
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereNull('user_id')
                       ->whereJsonContains('payload->phone', $user->phone);
              })
              // التبرعات المرتبطة بالاسم (للتبرعات القديمة)
             ->orWhere(function ($subQ) use ($user) {
                 $subQ->whereNull('user_id')
                      ->where('donor_name', $user->name);
             });
       })
       ->with(['program:id,title_ar,title_en', 'giftMeta', 'campaign:id,title_ar,title_en']);

        // تطبيق الفلاتر
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // إضافة إحصائيات (جميع التبرعات المرتبطة بالمستخدم) - قبل التصفح
        $userDonationsQuery = Donation::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereNull('user_id')
                       ->whereJsonContains('payload->phone', $user->phone);
              })
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereNull('user_id')
                       ->where('donor_name', $user->name);
              });
        });
        
        $stats = [
            'total_donations' => $userDonationsQuery->count(),
            'total_amount' => $userDonationsQuery->sum('amount'),
            'paid_donations' => $userDonationsQuery->where('status', 'paid')->count(),
            'pending_donations' => $userDonationsQuery->where('status', 'pending')->count(),
        ];

        // دعم التصفح (pagination)
        $page = $request->input('page', 1);
        $limit = $request->input('limit', $request->input('per_page', 50));
        $limit = min($limit, 100); // حد أقصى 100 عنصر في الصفحة
        
        // ترتيب النتائج
        $query->orderBy('created_at', 'desc');
        
        // الحصول على العدد الإجمالي قبل التصفح
        $total = $query->count();
        
        // تطبيق التصفح
        $donations = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'message' => 'Donations retrieved successfully',
            'data' => DonationResource::collection($donations),
            'stats' => $stats,
            'meta' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $limit),
                'from' => $total > 0 ? (($page - 1) * $limit) + 1 : 0,
                'to' => min($page * $limit, $total),
                'showing_all' => $total <= $limit && $page == 1,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/me/donations/{id}",
     *     summary="Get a specific donation for the current user",
     *     tags={"User Donations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Donation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Donation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donation retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/DonationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Donation not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // البحث عن التبرع مع التحقق من ملكية المستخدم
        $donation = Donation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere(function ($q) use ($user) {
                          $q->whereNull('user_id')
                            ->whereJsonContains('payload->phone', $user->phone);
                      })
                     ->orWhere(function ($q) use ($user) {
                         $q->whereNull('user_id')
                           ->where('donor_name', $user->name);
                     });
           })
           ->with(['program:id,title_ar,title_en', 'giftMeta', 'campaign:id,title_ar,title_en'])
           ->first();

        if (!$donation) {
            return response()->json([
                'message' => 'Donation not found or access denied',
            ], 404);
        }

        return response()->json([
            'message' => 'Donation retrieved successfully',
            'data' => new DonationResource($donation),
        ]);
    }
}
