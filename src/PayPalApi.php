<?php

namespace Puntodev\Payments;

use Illuminate\Http\Client\RequestException;

interface PayPalApi
{
    /**
     * @throws RequestException
     */
    public function createOrder(array $order): array;

    /**
     * @throws RequestException
     */
    public function findOrderById(string $id): ?array;

    /**
     * @throws RequestException
     */
    public function captureOrder(string $orderId): ?array;

    /**
     * @throws RequestException
     */
    public function verifyIpn(string $querystring): string;
}
