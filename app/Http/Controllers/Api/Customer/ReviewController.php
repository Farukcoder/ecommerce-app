<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the customer's written reviews.
     */
    public function index(Request $request): JsonResponse
    {
        $reviews = $request->user()->reviews()
            ->with(['product:id,name,thumbnail'])
            ->latest()
            ->paginate(15);

        return response()->json($reviews);
    }

    /**
     * Get products purchased by the customer that have not been reviewed yet.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get IDs of all products this user has already reviewed
        $reviewedProductIds = Review::where('user_id', $user->id)
            ->pluck('product_id')
            ->all();

        // Get delivered orders of this customer
        $orderIds = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->pluck('id')
            ->all();

        // Get unique products in these orders that have not been reviewed yet
        $pendingItems = OrderItem::whereIn('order_id', $orderIds)
            ->whereNotIn('product_id', $reviewedProductIds)
            ->with(['product:id,name,thumbnail'])
            ->select('product_id', 'order_id')
            ->groupBy('product_id', 'order_id')
            ->get()
            ->map(function ($item) {
                $order = Order::find($item->order_id);
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product ? $item->product->name : $item->product_name,
                    'order_number' => $order ? $order->order_number : '',
                    'thumbnail' => $item->product ? $item->product->thumbnail : null,
                ];
            });

        return response()->json($pendingItems);
    }

    /**
     * Store a newly created product review.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $productId = $validated['product_id'];

        // Ensure they actually bought the product and it has been delivered
        $hasPurchased = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'You can only review products that you have purchased and have been delivered.'
            ], 403);
        }

        // Check if already reviewed
        $alreadyReviewed = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'You have already reviewed this product.'
            ], 422);
        }

        $review = Review::create([
            'user_id'    => $user->id,
            'product_id' => $productId,
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review'  => $review
        ], 201);
    }
}
