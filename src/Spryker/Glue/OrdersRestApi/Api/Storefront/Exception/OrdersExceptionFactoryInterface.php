<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;

interface OrdersExceptionFactoryInterface
{
    public function createOrderNotFoundByReferenceException(): GlueApiException;

    public function createOrderReferenceNotSpecifiedException(): GlueApiException;

    public function createMissingAccessTokenException(): GlueApiException;

    public function createUnauthorizedCustomerException(): GlueApiException;

    public function createOrderItemNotFoundByUuidException(): GlueApiException;
}
