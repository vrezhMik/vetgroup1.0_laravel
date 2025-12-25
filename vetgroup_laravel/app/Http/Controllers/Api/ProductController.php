<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $start = (int) $request->query('start', 0);
        $limit = (int) $request->query('limit', 18);
        $categoryTitle = $request->query('cat');
        $search = trim((string) $request->query('search', ''));

        $query = Product::query()
            ->with('categories')
            ->orderByDesc('updated_at')
            ->orderBy('name');

        // Fetch all matching products from the database first, then
        // apply any category filtering in PHP to avoid MySQL collation
        // issues between connection and column collations.
        $products = $query->get();

        // Auto-assign categories for products that don't have any:
        // if a category title is present in the product name or description,
        // link that category to the product.
        $allCategories = Category::query()->get();

        foreach ($products as $product) {
            if ($product->categories->isNotEmpty()) {
                continue;
            }

            $name = mb_strtolower((string) $product->name, 'UTF-8');
            $description = mb_strtolower((string) ($product->description ?? ''), 'UTF-8');

            $matchedCategoryIds = [];

            foreach ($allCategories as $category) {
                $title = mb_strtolower((string) $category->title, 'UTF-8');

                if ($title === '') {
                    continue;
                }

                if (str_contains($name, $title) || str_contains($description, $title)) {
                    $matchedCategoryIds[] = $category->id;
                }
            }

            if ($matchedCategoryIds !== []) {
                $product->categories()->syncWithoutDetaching($matchedCategoryIds);
                $product->load('categories');
            }
        }

        if ($categoryTitle) {
            $products = $products
                ->filter(function (Product $product) use ($categoryTitle) {
                    return $product->categories->contains(function ($category) use ($categoryTitle): bool {
                        return (string) $category->title === (string) $categoryTitle;
                    });
                })
                ->values();
        }

        if ($search !== '') {
            $words = preg_split('/\s+/', $search) ?: [];

            $products = $products
                ->filter(function (Product $product) use ($words): bool {
                    $name = mb_strtolower((string) $product->name, 'UTF-8');
                    $description = mb_strtolower((string) ($product->description ?? ''), 'UTF-8');

                    foreach ($words as $word) {
                        $w = mb_strtolower((string) $word, 'UTF-8');

                        if ($w === '') {
                            continue;
                        }

                        if (! str_contains($name, $w) && ! str_contains($description, $w)) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();
        }

        if ($start > 0 || $limit > 0) {
            $products = $products
                ->slice($start, $limit > 0 ? $limit : null)
                ->values();
        }

        $data = $products->map(function (Product $product): array {
            $category = $product->categories->first();

            $imageUrl = null;

            if ($product->image) {
                $path = ltrim($product->image, '/');
                $path = preg_replace('#^uploads/#', '', $path);
                $imageUrl = Storage::disk('uploads')->url($path);
            }

            return [
                'code' => (string) $product->code,
                'name' => (string) $product->name,
                'description' => (string) ($product->description ?? ''),
                'price' => (float) ($product->price ?? 0),
                'backendId' => $product->backend_id ? (string) $product->backend_id : null,
                'stock' => (int) ($product->stock ?? 0),
                'pack_price' => (float) ($product->pack_price ?? 0),
                'image' => $imageUrl ? ['url' => (string) $imageUrl] : null,
                'qty' => 1,
                'totalPrice' => 0,
                '__typename' => 'Product',
                'category' => [
                    'title' => $category ? (string) $category->title : '',
                ],
            ];
        })->values();

        return response()->json([
            'products' => $data,
        ]);
    }
}
