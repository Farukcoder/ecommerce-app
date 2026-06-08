<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of product reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user']);

        // Search in product name, user name, or review comment
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('comment', 'like', "%{$search}%");
            });
        }

        // Filter by rating
        if ($rating = $request->get('rating')) {
            $query->where('rating', (int) $rating);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();
        $filters = $request->only(['search', 'rating']);

        return view('reviews.index', compact('reviews', 'filters'));
    }

    /**
     * Remove the specified review.
     */
    public function destroy(Review $review)
    {
        $productName = $review->product ? $review->product->name : 'Product';
        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', "Review for \"{$productName}\" deleted successfully.");
    }
}
