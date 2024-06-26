<?php

declare(strict_types=1);

namespace SmartDelivery\DeliveryService\Raketa\Providers;

use App\Providers\AppServiceProvider;
use SmartDelivery\DeliveryService\Raketa\Repositories\CreateOrderRepository;
use SmartDelivery\DeliveryService\Raketa\Repositories\Impl\CreateOrderRepositoryImpl;
use SmartDelivery\DeliveryService\Raketa\Repositories\Impl\OrderGroupRepositoryImpl;
use SmartDelivery\DeliveryService\Raketa\Repositories\OrderGroupRepository;
use SmartDelivery\DeliveryService\Raketa\Service\CreateOrderGroupService;
use SmartDelivery\DeliveryService\Raketa\Service\CreateOrderService;
use SmartDelivery\DeliveryService\Raketa\Service\FindGroupOrderByOrderIdService;
use SmartDelivery\DeliveryService\Raketa\Service\Impl\CreateOrderGroupServiceImpl;
use SmartDelivery\DeliveryService\Raketa\Service\Impl\CreateOrderServiceImpl;
use SmartDelivery\DeliveryService\Raketa\Service\Impl\FindGroupOrderByOrderIdServiceServiceImpl;
use SmartDelivery\DeliveryService\Raketa\UseCases\CreateOrderUseCase;
use SmartDelivery\DeliveryService\Raketa\UseCases\Impl\CreateOrderUseCaseImpl;
use SmartDelivery\DeliveryService\Raketa\UseCases\Impl\SendCourierInfoUseCaseImpl;
use SmartDelivery\DeliveryService\Raketa\UseCases\SendCourierInfoUseCase;

final class RaketaDIServiceProvider extends AppServiceProvider
{
    public array $bindings = [
        //Repositories
        CreateOrderRepository::class => CreateOrderRepositoryImpl::class,
        OrderGroupRepository::class => OrderGroupRepositoryImpl::class,

        //Services
        CreateOrderService::class => CreateOrderServiceImpl::class,
        CreateOrderGroupService::class => CreateOrderGroupServiceImpl::class,
        FindGroupOrderByOrderIdService::class => FindGroupOrderByOrderIdServiceServiceImpl::class,

        //Use Cases
        CreateOrderUseCase::class => CreateOrderUseCaseImpl::class,
        SendCourierInfoUseCase::class => SendCourierInfoUseCaseImpl::class,
    ];
}
