<?php

declare(strict_types=1);

namespace SmartDelivery\Order\UseCases;

use SmartDelivery\Order\Dto\RequestOrderDto;

interface CreateOrderUseCase
{
    public function handle(RequestOrderDto $dto): void;
}
