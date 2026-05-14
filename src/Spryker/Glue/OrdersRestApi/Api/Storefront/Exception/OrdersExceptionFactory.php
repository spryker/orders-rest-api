<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\OrdersRestApi\OrdersRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class OrdersExceptionFactory implements OrdersExceptionFactoryInterface
{
    protected const string ERROR_CODE_MISSING_ACCESS_TOKEN = '002';

    protected const string ERROR_DETAIL_MISSING_ACCESS_TOKEN = 'Missing access token.';

    protected const string ERROR_DETAIL_ORDER_REFERENCE_NOT_SPECIFIED = 'Order reference is not specified.';

    public function createOrderNotFoundByReferenceException(): GlueApiException
    {
        return $this->createOrderNotFoundException();
    }

    public function createOrderReferenceNotSpecifiedException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            OrdersRestApiConfig::RESPONSE_CODE_CANT_FIND_ORDER,
            static::ERROR_DETAIL_ORDER_REFERENCE_NOT_SPECIFIED,
        );
    }

    public function createMissingAccessTokenException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNAUTHORIZED,
            static::ERROR_CODE_MISSING_ACCESS_TOKEN,
            static::ERROR_DETAIL_MISSING_ACCESS_TOKEN,
        );
    }

    public function createUnauthorizedCustomerException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_FORBIDDEN,
            OrdersRestApiConfig::RESPONSE_CODE_CUSTOMER_UNAUTHORIZED,
            OrdersRestApiConfig::RESPONSE_DETAILS_CUSTOMER_UNAUTHORIZED,
        );
    }

    public function createOrderItemNotFoundByUuidException(): GlueApiException
    {
        return $this->createOrderNotFoundException();
    }

    protected function createOrderNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            OrdersRestApiConfig::RESPONSE_CODE_CANT_FIND_ORDER,
            OrdersRestApiConfig::RESPONSE_DETAIL_CANT_FIND_ORDER,
        );
    }
}
