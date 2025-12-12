<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $externalPayload = [
            'TransactionDate' => $transactionDate,
            'ClientID' => $validated['client_code'],
            'ItemsList' => $itemsList,
            'Note' => '',
        ];

        $externalResponse = Http::withBasicAuth('001', '001')
            ->asJson()
            ->post('http://87.241.165.71:8081/web/hs/Eportal/Order', $externalPayload);

        if ($externalResponse->successful()) {
            $order->complited = true;
            $order->save();
        }

        return response()->json([
            'order_id' => $order->order_id,
            'complited' => (bool) $order->complited,
            'external' => [
                'status' => $externalResponse->status(),
                'body' => $externalResponse->json() ?? $externalResponse->body(),
            ],
        ], $externalResponse->successful() ? 201 : 202);
    }

    public function show(Order $order): JsonResponse
    {
        $productsArray = null;

        // Prefer the structured products_json payload if present
        if (is_array($order->products_json)) {
            $productsArray = $order->products_json;
        } elseif (is_string($order->products_json)) {
            $decoded = json_decode($order->products_json, true);
            if (is_array($decoded)) {
                $productsArray = $decoded;
            }
        }

        // Fallback to products field if needed
        if ($productsArray === null) {
            if (is_array($order->products)) {
                $productsArray = $order->products;
            } elseif (is_string($order->products)) {
                $decoded = json_decode($order->products, true);
                if (is_array($decoded)) {
                    $productsArray = $decoded;
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
