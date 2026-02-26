<?php

namespace App\Listeners;

use App\Events\PaymentPaid;
use App\Services\LoyaltyEngine;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePaymentPaid
{
    public function __construct(
        protected LoyaltyEngine $loyaltyEngine
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PaymentPaid $event): void
    {
        $this->loyaltyEngine->processPayment($event->payment);
    }
}
