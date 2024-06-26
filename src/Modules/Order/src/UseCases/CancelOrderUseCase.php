<?php

declare(strict_types=1);

namespace SmartDelivery\Order\UseCases;

use SmartDelivery\Order\Dto\CancelOrderDto;

interface CancelOrderUseCase
{
    public function handle(CancelOrderDto $dto): void;
}
