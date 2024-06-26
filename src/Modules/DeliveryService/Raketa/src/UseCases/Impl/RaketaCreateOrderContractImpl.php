<?php

declare(strict_types=1);

namespace SmartDelivery\DeliveryService\Raketa\UseCases\Impl;

use SmartDelivery\DeliveryService\Main\Contracts\CreateOrderContract;
use SmartDelivery\DeliveryService\Main\Dto\CreateExternalOrderDto;
use SmartDelivery\DeliveryService\Main\Dto\WarehouseTypeEnum;
use SmartDelivery\DeliveryService\Main\Enums\DeliveryServiceEnum;
use SmartDelivery\DeliveryService\Raketa\Dto\ContactInfoDto;
use SmartDelivery\DeliveryService\Raketa\Dto\CreateOrderDto;
use SmartDelivery\DeliveryService\Raketa\Dto\OrderGroupDto;
use SmartDelivery\DeliveryService\Raketa\Dto\PointDto;
use SmartDelivery\DeliveryService\Raketa\Dto\ProductDto;
use SmartDelivery\DeliveryService\Raketa\Dto\TaskDto;
use SmartDelivery\DeliveryService\Raketa\Service\CreateOrderGroupService;
use SmartDelivery\HttpClients\Raketa\Enums\TransportTypeEnum;
use SmartDelivery\HttpClients\Raketa\RaketaHttpClientInterface;

final readonly class RaketaCreateOrderContractImpl implements CreateOrderContract
{
    public function __construct(
        private RaketaHttpClientInterface $httpClient,
        private CreateOrderGroupService $createOrderGroupService
    ) {}

    public function handle(CreateExternalOrderDto $externalOrderDto): void
    {
        $finalPoint = new PointDto(
            contact_info: new ContactInfoDto(phone_number: $externalOrderDto->recipient_phone),
            address: $externalOrderDto->delivery_address,
        );

        $startPoint = array_map(function (ProductDto $productDto) use ($externalOrderDto) {
            return new PointDto(
                contact_info: new ContactInfoDto(phone_number: $externalOrderDto->sender_phone),
                address: $productDto->address,
                items: [new ProductDto(
                    title: $productDto->title,
                    price: $productDto->price,
                    count: $productDto->count,
                    address: null,
                    warehouse_type: null
                )],
                merchant_order_id: $externalOrderDto->warehouse_order_id,
                tasks: [new TaskDto(
                    id: $productDto->warehouse_type === WarehouseTypeEnum::EXTERNAL ? 11398 : null,
                )]
            );
        }, $externalOrderDto->items);

        $response = $this->httpClient->createOrder(
            new CreateOrderDto(
                transportType: TransportTypeEnum::CAR,
                points: array_map(
                    (fn(PointDto $point) => $point->toArray()),
                    array_merge($startPoint, [$finalPoint])
                ),
                callbackUrl: route('status-hook.order'),
                orderPlannedAt: $externalOrderDto->order_planned_at
            )
        );

        $this->createOrderGroupService->handle(new OrderGroupDto(
            order_id: $response->group_id,
            external_order_id: $externalOrderDto->external_order_id,
            status: $response->status
        ));
    }

    public function getProvider(): DeliveryServiceEnum
    {
        return DeliveryServiceEnum::RAKETA;
    }
}
