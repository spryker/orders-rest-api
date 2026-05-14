<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Mapper;

use ArrayObject;
use Generated\Api\Storefront\CustomersOrdersStorefrontResource;
use Generated\Api\Storefront\OrderItemsStorefrontResource;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer;
use Generated\Shared\Transfer\RestOrderItemsAttributesTransfer;

interface OrderStorefrontMapperInterface
{
    public function mapOrderTransferToCustomersOrdersResource(
        OrderTransfer $orderTransfer,
        CustomersOrdersStorefrontResource $customersOrdersStorefrontResource,
    ): CustomersOrdersStorefrontResource;

    public function mapItemTransferToOrderItemsStorefrontResource(
        ItemTransfer $itemTransfer,
        OrderItemsStorefrontResource $orderItemsStorefrontResource,
    ): OrderItemsStorefrontResource;

    /**
     * @return array<string, mixed>
     */
    public function mapTotals(OrderTransfer $orderTransfer): array;

    public function mapItemTransferToRestOrderItemsAttributesTransfer(
        ItemTransfer $itemTransfer,
        RestOrderItemsAttributesTransfer $restOrderItemsAttributesTransfer,
    ): RestOrderItemsAttributesTransfer;

    public function mapOrderTransferToRestOrderDetailsAttributesTransfer(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer,
    ): RestOrderDetailsAttributesTransfer;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapOrderExpenses(OrderTransfer $orderTransfer): array;

    /**
     * @return array<string, mixed>
     */
    public function mapBillingAddress(OrderTransfer $orderTransfer): array;

    /**
     * @return array<string, mixed>|null
     */
    public function mapShippingAddress(OrderTransfer $orderTransfer): ?array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapShipments(OrderTransfer $orderTransfer): array;

    /**
     * @param \ArrayObject<int, \Spryker\Shared\Kernel\Transfer\AbstractTransfer> $transferCollection
     *
     * @return array<int, array<string, mixed>>
     */
    public function convertTransferCollectionToArray(ArrayObject $transferCollection): array;
}
