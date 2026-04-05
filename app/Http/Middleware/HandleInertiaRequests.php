<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'notifications' => fn () => $this->getNotifications($request),
        ];
    }

    /**
     * Format notifications for frontend consumption.
     * Cached on first page load with `once()` in Inertia to prevent refetching.
     */
    private function getNotifications(Request $request): array
    {
        if (! $request->user()) {
            return [];
        }

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(50)
            ->get();

        return $notifications->map(function ($notification) {
            $data = $notification->data;

            // Ensure slug is present
            if (! isset($data['product_slug']) && isset($data['product_id'])) {
                $product = Product::find($data['product_id']);
                if ($product) {
                    $data['product_slug'] = $product->slug;
                }
            }

            return [
                'id' => $notification->id,
                'slug' => $data['product_slug'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'message' => $data['message'] ?? null,
                'product_name' => $data['product_name'] ?? null,
                'current_stock' => $data['current_stock'] ?? null,
                'alert_level' => $data['alert_level'] ?? null,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        })->toArray();
    }
}
