<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\OrdersRestApi;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\Kernel\AbstractBundleConfig;
use Symfony\Component\HttpFoundation\Response;

class OrdersRestApiConfig extends AbstractBundleConfig
{
    public const RESOURCE_ORDERS = 'orders';
    public const RESOURCE_MY_ORDERS = 'my-orders';
    public const RESOURCE_ORDER_ITEMS = 'order-items';
    public const RESOURCE_CUSTOMERS = 'customers';

    public const RESOURCE_ORDERS_IS_PROTECTED = true;

    public const RESPONSE_CODE_CANT_FIND_ORDER = '801';
    public const RESPONSE_CODE_CUSTOMER_UNAUTHORIZED = '802';

    public const RESPONSE_DETAIL_CANT_FIND_ORDER = 'Can\'t find order by the given order reference';
    public const RESPONSE_DETAILS_CUSTOMER_UNAUTHORIZED = 'Unauthorized request.';

    /**
     * @api
     *
     * @return mixed[]
     */
    public function getCantFindOrderRestError(): array
    {
        return [
            RestErrorMessageTransfer::CODE => static::RESPONSE_CODE_CANT_FIND_ORDER,
            RestErrorMessageTransfer::DETAIL => static::RESPONSE_DETAIL_CANT_FIND_ORDER,
            RestErrorMessageTransfer::STATUS => Response::HTTP_NOT_FOUND,
        ];
    }

    /**
     * @api
     *
     * @return mixed[]
     */
    public function getCustomerUnauthorizedRestError(): array
    {
        return [
            RestErrorMessageTransfer::CODE => static::RESPONSE_CODE_CUSTOMER_UNAUTHORIZED,
            RestErrorMessageTransfer::DETAIL => static::RESPONSE_DETAILS_CUSTOMER_UNAUTHORIZED,
            RestErrorMessageTransfer::STATUS => Response::HTTP_FORBIDDEN,
        ];
    }
}
