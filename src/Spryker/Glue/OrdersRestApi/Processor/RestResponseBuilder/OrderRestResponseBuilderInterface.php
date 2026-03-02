<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\OrdersRestApi\Processor\RestResponseBuilder;

use ArrayObject;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface OrderRestResponseBuilderInterface
{
    public function createOrderRestResource(OrderTransfer $orderTransfer): RestResourceInterface;

    public function createOrderRestResponse(OrderTransfer $orderTransfer): RestResponseInterface;

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\OrderTransfer> $orderTransfers
     * @param int $totalItems
     * @param int $limit
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createOrderListRestResponse(ArrayObject $orderTransfers, int $totalItems, int $limit): RestResponseInterface;

    public function createOrderNotFoundErrorResponse(): RestResponseInterface;

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function createMappedOrderItemRestResourcesFromOrderItemTransfers(ArrayObject $itemTransfers): array;

    public function createCustomerUnauthorizedErrorResponse(): RestResponseInterface;

    public function createErrorResponse(RestErrorMessageTransfer $restErrorMessageTransfer): RestResponseInterface;
}
