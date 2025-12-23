<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Use the Vetgroup user ID (same as vetgroup_users.id) to
        // fetch orders for the currently logged-in user.
        $userId = $request->query('userId');

        if (! $userId) {
            return response()->json([
                'message' => 'Missing userId parameter.',
            ], 422);
        }

        $orders = Order::query()
            ->where('vetgroup_user_id', $userId)
            ->orderByDesc('created')
            ->get([
                'id',
                'order_id',
                'total',
                'created',
                'products_json',
            ]);

        return response()->json([
            'orders' => $orders->map(fn (Order $order): array => [
                'id' => (int) $order->id,
                'order_id' => (string) $order->order_id,
                'total' => (float) ($order->total ?? 0),
                'created' => $order->created ? $order->created->format('Y-m-d H:i:s') : null,
                'products_json' => $order->products_json,
            ])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total' => ['required', 'numeric'],
            'vetgroup_user_id' => ['required', 'integer'],
            'client_code' => ['required', 'string'],
            'products' => ['required', 'array'],
            'products.*.name' => ['required', 'string'],
            'products.*.description' => ['nullable', 'string'],
            'products.*.qty' => ['required', 'numeric'],
            'products.*.price' => ['required', 'numeric'],
            'items_list' => ['required', 'array'],
            'items_list.*.ItemID' => ['required', 'string'],
            'items_list.*.Quantity' => ['required', 'numeric'],
        ]);

        $productsCount = count($validated['products'] ?? []);
        $itemsListCount = count($validated['items_list'] ?? []);

        if ($productsCount !== $itemsListCount) {
            return response()->json([
                'message' => 'The products and items_list counts must match.',
                'products_count' => $productsCount,
                'items_list_count' => $itemsListCount,
            ], 422);
        }

        $transactionDate = Carbon::now()->format('d:m:Y H:i:s');

        $itemsList = collect($validated['items_list'])
            ->map(fn (array $item): array => [
                'ItemID' => $item['ItemID'],
                'Quantity' => $item['Quantity'],
            ])
            ->values()
            ->all();

        $jsonFullData = [
            'TransactionDate' => $transactionDate,
            'ClientID' => $validated['client_code'],
            'ItemsList' => $itemsList,
            'Note' => '',
        ];

        $orderId = 'ORD-'.Str::upper(Str::random(6));
        $createdAt = Carbon::now();

        $externalPayload = [
            'TransactionDate' => $transactionDate,
            'ClientID' => $validated['client_code'],
            'ItemsList' => $itemsList,
            'Note' => '',
        ];

        $order = null;
        $externalResponse = null;

        try {
            try {
                $order = Order::create([
                    'document_id' => $validated['client_code'],
                    'order_id' => $orderId,
                    'vetgroup_user_id' => $validated['vetgroup_user_id'],
                    'user_id' => $validated['vetgroup_user_id'],
                    'created' => $createdAt,
                    'total' => $validated['total'],
                    'products' => $validated['products'],
                    'products_json' => $jsonFullData,
                    'complited' => false,
                ]);
            } catch (QueryException $exception) {
                Log::warning('Order create failed, retrying with JSON-encoded payloads.', [
                    'order_id' => $orderId,
                    'vetgroup_user_id' => $validated['vetgroup_user_id'],
                    'client_code' => $validated['client_code'],
                    'products_count' => $productsCount,
                    'items_list_count' => $itemsListCount,
                    'exception_message' => $exception->getMessage(),
                ]);

                $orderIdValue = DB::table('orders')->insertGetId([
                    'document_id' => $validated['client_code'],
                    'order_id' => $orderId,
                    'vetgroup_user_id' => $validated['vetgroup_user_id'],
                    'user_id' => $validated['vetgroup_user_id'],
                    'created' => $createdAt,
                    'total' => $validated['total'],
                    'products' => json_encode($validated['products'], JSON_UNESCAPED_UNICODE),
                    'products_json' => json_encode($jsonFullData, JSON_UNESCAPED_UNICODE),
                    'complited' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $order = Order::query()->findOrFail($orderIdValue);
            }

            try {
                $externalResponse = Http::withBasicAuth('001', '001')
                    ->asJson()
                    ->post('http://87.241.165.71:8081/web/hs/Eportal/Order', $externalPayload);
            } catch (\Throwable $exception) {
                Log::error('External order API call failed.', [
                    'order_id' => $orderId,
                    'vetgroup_user_id' => $validated['vetgroup_user_id'],
                    'client_code' => $validated['client_code'],
                    'products_count' => $productsCount,
                    'items_list_count' => $itemsListCount,
                    'exception_message' => $exception->getMessage(),
                ]);
            }

            if ($externalResponse && $externalResponse->successful()) {
                $order->complited = true;
                $order->save();
            }
        } catch (\Throwable $exception) {
            Log::error('Unexpected error while creating order.', [
                'order_id' => $orderId,
                'vetgroup_user_id' => $validated['vetgroup_user_id'],
                'client_code' => $validated['client_code'],
                'products_count' => $productsCount,
                'items_list_count' => $itemsListCount,
                'exception_message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to create order at this time.',
            ], 500);
        }

        $statusCode = $externalResponse && $externalResponse->successful() ? 201 : 202;

        return response()->json([
            'order_id' => $order->order_id,
            'complited' => (bool) $order->complited,
            'external' => [
                'status' => $externalResponse ? $externalResponse->status() : null,
                'body' => $externalResponse
                    ? ($externalResponse->json() ?? $externalResponse->body())
                    : ['message' => 'External service unavailable'],
            ],
        ], $statusCode);
    }

    public function show(Order $order): JsonResponse
    {
        $productsArray = null;

        // Prefer the products column, which is expected to hold
        // the actual order line items (name, description, qty, price).
        if (is_array($order->products) && ! empty($order->products)) {
            $productsArray = $order->products;
        } elseif (is_string($order->products)) {
            $decoded = json_decode($order->products, true);
            if (is_array($decoded) && ! empty($decoded)) {
                $productsArray = $decoded;
            }
        }

        // Fallback to products_json for legacy data.
        if ($productsArray === null) {
            $payload = $order->products_json;

            if (is_string($payload)) {
                $decoded = json_decode($payload, true);
                $payload = is_array($decoded) ? $decoded : null;
            }

            if (is_array($payload)) {
                // If this is the 1C-style envelope with an ItemsList,
                // expose ItemsList as the products array so the frontend
                // can render it.
                if (array_key_exists('ItemsList', $payload) && is_array($payload['ItemsList'])) {
                    $productsArray = $payload['ItemsList'];
                } else {
                    $productsArray = $payload;
                }
            }
        }

        return response()->json([
            'id' => (int) $order->id,
            'order_id' => (string) $order->order_id,
            'total' => (float) ($order->total ?? 0),
            'created' => $order->created ? $order->created->format('Y-m-d H:i:s') : null,
            'products' => $productsArray,
            'products_json' => $order->products_json,
        ]);
    }
}
