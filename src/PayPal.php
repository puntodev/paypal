<?php

namespace Puntodev\Payments;

interface PayPal
{
    public function defaultClient(): PayPalApi;

    public function withCredentials(string $clientId, string $clientSecret): PayPalApi;
}
