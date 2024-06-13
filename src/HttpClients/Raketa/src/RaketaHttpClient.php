<?php

declare(strict_types=1);

namespace SmartDelivery\HttpClients\Raketa;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use SmartDelivery\DeliveryService\Raketa\Dto\CreateOrderDto;
use SmartDelivery\HttpClients\Raketa\DTO\AccessTokenDto;
use SmartDelivery\HttpClients\Raketa\Entities\UnexpectedErrorException;
use SmartDelivery\HttpClients\Raketa\Enums\OrderGroupStatusEnum;
use SmartDelivery\HttpClients\Raketa\Responses\OrderGroupResponse;
use SmartDelivery\HttpClients\Raketa\Responses\GetAccessTokenResponse;
use SmartDelivery\HttpClients\Raketa\Responses\OrderResponse;
use SmartDelivery\HttpClients\Raketa\Services\GetAccessTokenService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class RaketaHttpClient implements RaketaHttpClientInterface
{
    private const GET_ACCESS_TOKEN = '/api-gate/v0/common/api-token-refresh';
    private const CREATE_ORDER = '/api-gate/v0/deliveries/groups';
    private const GET_ORDER_DETAIL = '/api-gate/v0/deliveries/groups/group_id';
    private const CANCEL_ORDER = '/api-gate/v0/deliveries/cancel-group/group_id';

    public function __construct(
        private GetAccessTokenService $getAccessTokenService,
        private Client $client,
        private string $apiUrl,
        private string $accessToken,
        private string $refreshAccessToken,
    ) {}

    public function getHeaders(): array
    {
        return [
            'Authorization' => 'JWT ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    }

    public function getAccessToken(): string
    {
        return $this->getAccessTokenService->handle($this->accessToken, $this->refreshAccessToken);
    }

    public function validateResponse(ResponseInterface $response): void {
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new UnexpectedErrorException(
                $response->getBody()->getContents(),
                $response->getStatusCode()
            );
        }
    }

    public function createOrder(CreateOrderDto $createOrderDto): OrderGroupResponse
    {
        $formParams = [
            'transport_type' => $createOrderDto->transportType->value,
            'callback_url' => $createOrderDto->callbackUrl,
            'points' => $createOrderDto->points
        ];

        try {
            $response = $this->client->request('POST', $this->apiUrl . self::CREATE_ORDER, [
                RequestOptions::JSON => $formParams,
                'headers' => $this->getHeaders()
            ]);
        } catch (GuzzleException $e) {
            Log::critical('Request params', $formParams);
            throw new UnexpectedErrorException($e->getMessage(), 0, $e);
        }
        try {
            $this->validateResponse($response);
        } catch (UnexpectedErrorException $e) {
            Log::critical('Request params', $formParams);
            throw  $e;
        }

        try {
            $responseBody = $response->getBody()->getContents();
            $responseBodyArr = json_decode($responseBody, true);

            return new OrderGroupResponse(
                group_id: $responseBodyArr['group_detail']['id'],
                status: OrderGroupStatusEnum::tryFrom((string) $responseBodyArr['group_detail']['status']),
                orders: array_map(
                    fn ($order) => new OrderResponse(
                        id: $order['id'],
                        status: $order['status'],
                        price: $order['price'],
                        sms_code: $order['sms_code'],
                        merchant_order_id: $order['merchant_order_id'],
                        tracking_short_link: $order['tracking_short_link'],
                        tracking_uuid: $order['tracking_uuid'],
                        courier_name: $order['courier_name'],
                        courier_phone: $order['courier_phone'],
                        was_returned: $order['was_returned'],
                    ),
                    $responseBodyArr['group_detail']['orders']
                )
            );
        } catch (Throwable $e) {
            Log::critical('Request params', $formParams);
            throw new UnexpectedErrorException($e->getMessage(), 0, $e);
        }
    }

    public function getOrderDetail(string $orderId): OrderResponse
    {
        // TODO: Implement getOrderDetail() method.
    }

    public function cancelOrder(string $orderId): void
    {
        // TODO: Implement cancelOrder() method.
    }

    public function obtainAccessToken(AccessTokenDto $accessTokenDto): GetAccessTokenResponse
    {
        $formParams = [
            'refresh' => $accessTokenDto->refresh_token,
        ];

        try {
            $response = $this->client->request('POST', $this->apiUrl . self::GET_ACCESS_TOKEN, [
                RequestOptions::JSON => $formParams,
                'headers' => $this->getHeaders()
            ]);
        } catch (GuzzleException $e) {
            throw new UnexpectedErrorException($e->getMessage(), 0, $e);
        }

        $this->validateResponse($response);

        try {
            $responseBody = $response->getBody()->getContents();
            $responseBodyArr = json_decode($responseBody, true);

            return new GetAccessTokenResponse(
                access: $responseBodyArr['access'],
                refresh: $responseBodyArr['refresh']
            );
        } catch (Throwable $e) {
            throw new UnexpectedErrorException($e->getMessage(), 0, $e);
        }
    }
}