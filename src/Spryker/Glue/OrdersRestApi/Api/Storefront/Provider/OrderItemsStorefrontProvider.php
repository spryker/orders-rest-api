<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\OrderItemsStorefrontResource;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
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
        $orderReference = (string)$this->findUriVariable(static::KEY_ORDER_REFERENCE);
        $uuid = (string)$this->findUriVariable(static::KEY_UUID);

        $orderTransfer = $this->salesClient->getCustomerOrderByOrderReference(
            (new OrderTransfer())
                ->setOrderReference($orderReference)
                ->setCustomerReference($customerReference),
        );

        $itemTransfer = $this->findOrderItemByUuid($orderTransfer, $uuid);

        if ($itemTransfer === null) {
            throw $this->exceptionFactory->createOrderItemNotFoundByUuidException();
        }

        return $this->orderStorefrontMapper->mapItemTransferToOrderItemsStorefrontResource(
            $itemTransfer,
            new OrderItemsStorefrontResource(),
        );
    }

    protected function findOrderItemByUuid(OrderTransfer $orderTransfer, string $uuid): ?ItemTransfer
    {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getUuid() === $uuid) {
                return $itemTransfer->setOrderReference($orderTransfer->getOrderReference());
            }
        }

        return null;
    }
}
