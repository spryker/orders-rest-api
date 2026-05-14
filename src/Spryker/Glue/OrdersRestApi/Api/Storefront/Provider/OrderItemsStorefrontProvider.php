<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\OrderItemsStorefrontResource;
use Generated\Shared\Transfer\OrderItemFilterTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Sales\SalesClientInterface;
use Spryker\Glue\OrdersRestApi\Api\Storefront\Exception\OrdersExceptionFactoryInterface;
use Spryker\Glue\OrdersRestApi\Api\Storefront\Mapper\OrderStorefrontMapperInterface;

class OrderItemsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string KEY_ORDER_REFERENCE = 'orderReference';

    protected const string KEY_UUID = 'uuid';

    public function __construct(
        protected SalesClientInterface $salesClient,
        protected OrdersExceptionFactoryInterface $exceptionFactory,
        protected OrderStorefrontMapperInterface $orderStorefrontMapper,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createMissingAccessTokenException();
        }

        $customerReference = $this->getCustomerReference();
        $orderReference = (string)($this->getUriVariables()[static::KEY_ORDER_REFERENCE] ?? '');
        $uuid = (string)($this->getUriVariables()[static::KEY_UUID] ?? '');

        $orderItemFilterTransfer = (new OrderItemFilterTransfer())
            ->addSalesOrderItemUuid($uuid)
            ->addCustomerReference($customerReference);

        $items = $this->salesClient->getOrderItems($orderItemFilterTransfer)->getItems();

        if ($items->count() === 0) {
            throw $this->exceptionFactory->createOrderItemNotFoundByUuidException();
        }

        /** @var \Generated\Shared\Transfer\ItemTransfer $itemTransfer */
        $itemTransfer = $items->offsetGet(0);

        if ($itemTransfer->getOrderReference() !== $orderReference) {
            throw $this->exceptionFactory->createOrderItemNotFoundByUuidException();
        }

        return $this->orderStorefrontMapper->mapItemTransferToOrderItemsStorefrontResource(
            $itemTransfer,
            new OrderItemsStorefrontResource(),
        );
    }
}
