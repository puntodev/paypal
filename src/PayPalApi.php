<?php

namespace Puntodev\Payments;

use Illuminate\Http\Client\RequestException;

interface PayPalApi
{
    /**
     * @param array $order
     * @return array
     * @throws RequestException
     */
    public function createOrder(array $order): array;

    /**
     * @param string $id
     * @return array|null
     * @throws RequestException
     */
    public function findOrderById(string $id): ?array;

    /**
     * @param string $orderId
     * @return array|null
     * @throws RequestException
     */
    public function captureOrder(string $orderId): ?array;

    /**
     * @param string $querystring
     * @return string
     * @throws RequestException
     */
    public function verifyIpn(string $querystring): string;
}
