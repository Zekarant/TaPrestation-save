<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use App\Models\DeliveryDriver;
use Illuminate\Notifications\Notification;

class DriverAcceptedDelivery extends Notification
{
    public FoodOrder $order;
    public DeliveryDriver $driver;

    public function __construct(FoodOrder $order, DeliveryDriver $driver)
    {
        $this->order = $order;
        $this->driver = $driver;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $driverName = $this->driver->user->name ?? 'Un livreur';
        
        return [
            'type' => 'driver_accepted_delivery',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'driver_id' => $this->driver->id,
            'driver_name' => $driverName,
            'message' => "🚗 {$driverName} a accepté de livrer la commande #{$this->order->order_number}. Vous pouvez maintenant commencer la préparation !",
            'url' => route('food-orders.show', $this->order),
        ];
    }
}
