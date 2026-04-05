<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;

class NotifyStockAlert
{
    /**
     * Execute the action.
     */
    public function execute(Product $product): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Check if user already has an unread notification for this product
            $hasUnread = $user->unreadNotifications()
                ->where('data->product_id', $product->id)
                ->where('type', StockAlertNotification::class)
                ->exists();

            if (! $hasUnread) {
                $user->notify(new StockAlertNotification($product));
            }
        }
    }
}
