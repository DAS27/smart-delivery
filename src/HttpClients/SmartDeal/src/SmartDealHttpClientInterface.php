<?php

declare(strict_types=1);

namespace SmartDelivery\HttpClients\SmartDeal;

use SmartDelivery\HttpClients\SmartDeal\Dto\CourierInfoDto;

interface SmartDealHttpClientInterface
{
    public function sendCourierInfo(CourierInfoDto $courierInfoDto): void;
}
