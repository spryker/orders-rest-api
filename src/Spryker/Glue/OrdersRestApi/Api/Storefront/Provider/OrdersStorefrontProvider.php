<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\OrdersStorefrontResource;
use Generated\Shared\Transfer\OrderListRequestTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Sales\SalesClientInterface;
use Spryker\Glue\OrdersRestApi\Api\Storefront\Exception\OrdersExceptionFactoryInterface;
use Spryker\Glue\OrdersRestApi\Api\Storefront\Mapper\OrderStorefrontMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class OrdersStorefrontProvider extends AbstractStorefrontProvider
{
    protected const int DEFAULT_LIMIT = 10;

    protected const string KEY_ORDER_REFERENCE = 'orderReference';

    public function __construct(
        protected SalesClientInterface $salesClient,
        protected OrdersExceptionFactoryInterface $exceptionFactory,
        protected OrderStorefrontMapperInterface $orderStorefrontMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): object|null
    {
        $orderReference = $this->getUriVariables()[static::KEY_ORDER_REFERENCE] ?? null;

        if ($orderReference === null) {
            throw $this->exceptionFactory->createOrderReferenceNotSpecifiedException();
        }

        $customerReference = $this->assertCustomerAuthenticated();

        $orderTransfer = (new OrderTransfer())
            ->setOrderReference($orderReference)
            ->setCustomerReference($customerReference);

        $orderTransfer = $this->salesClient->getCustomerOrderByOrderReference($orderTransfer);

        if ($orderTransfer->getIdSalesOrder() === null) {
            throw $this->exceptionFactory->createOrderNotFoundByReferenceException();
        }

        return $this->prepareOrderDetailResource($orderTransfer);
    }

    /**
     * @return array<\Generated\Api\Storefront\OrdersStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $customerReference = $this->assertCustomerAuthenticated();

        $filterTransfer = $this->buildFilterTransfer();

        $request = $this->getRequest();
        $sortField = $request->query->get('sort');

        if ($sortField !== null) {
            $direction = str_starts_with((string)$sortField, '-') ? 'DESC' : 'ASC';
            $field = ltrim((string)$sortField, '-');
            $filterTransfer->setOrderDirection($direction)->setOrderBy($field);
        }

        $orderListRequestTransfer = (new OrderListRequestTransfer())
            ->setCustomerReference($customerReference)
            ->setFilter($filterTransfer);

        $orderListTransfer = $this->salesClient->getOffsetPaginatedCustomerOrderList($orderListRequestTransfer);

        $resources = [];

        foreach ($orderListTransfer->getOrders() as $orderTransfer) {
            $resources[] = $this->prepareOrderListResource($orderTransfer);
        }

        $pagination = $orderListTransfer->getPagination();

        if ($pagination !== null && count($resources) > 0) {
            $nbResults = $pagination->getNbResults() ?? 0;
            $resources[0]->pagination = $this->calculatePagination($filterTransfer->getOffsetOrFail(), $filterTransfer->getLimitOrFail(), $nbResults);
        }

        return $resources;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertCustomerAuthenticated(): string
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createMissingAccessTokenException();
        }

        return $this->getCustomerReference();
    }

    protected function prepareOrderListResource(OrderTransfer $orderTransfer): OrdersStorefrontResource
    {
        $resource = new OrdersStorefrontResource();
        $resource->orderReference = $orderTransfer->getOrderReference();
        $resource->createdAt = $orderTransfer->getCreatedAt();
        $resource->currencyIsoCode = $orderTransfer->getCurrencyIsoCode();
        $resource->priceMode = $orderTransfer->getPriceMode();
        $resource->totals = $this->orderStorefrontMapper->mapTotals($orderTransfer);
        $resource->context = $orderTransfer->toArray(true, true);

        return $resource;
    }

    protected function prepareOrderDetailResource(OrderTransfer $orderTransfer): OrdersStorefrontResource
    {
        $orderDetailsAttributesTransfer = $this->orderStorefrontMapper->mapOrderTransferToRestOrderDetailsAttributesTransfer(
            $orderTransfer,
            new RestOrderDetailsAttributesTransfer(),
        );
        $orderDetailsAttributesData = $orderDetailsAttributesTransfer->toArray(true, true);

        $resource = $this->serializer->denormalize([
            ...$orderDetailsAttributesData,
            'orderReference' => $orderTransfer->getOrderReference(),
            'totals' => $this->orderStorefrontMapper->mapTotals($orderTransfer),
            'billingAddress' => $this->orderStorefrontMapper->mapBillingAddress($orderTransfer),
            'createdAt' => $orderTransfer->getCreatedAt(),
            'currencyIsoCode' => $orderTransfer->getCurrencyIsoCode(),
            'priceMode' => $orderTransfer->getPriceMode(),
            'merchantReferences' => $orderTransfer->getMerchantReferences(),
            'expenses' => $this->orderStorefrontMapper->mapOrderExpenses($orderTransfer),
            'shippingAddress' => $this->hasSplitShipment($orderTransfer)
                ? null : $this->orderStorefrontMapper->mapShippingAddress($orderTransfer),
            'payments' => $this->orderStorefrontMapper->convertTransferCollectionToArray($orderTransfer->getPayments()),
            'shipments' => $this->orderStorefrontMapper->mapShipments($orderTransfer),
            'calculatedDiscounts' => $this->orderStorefrontMapper->convertTransferCollectionToArray($orderTransfer->getCalculatedDiscounts()),
            'context' => $orderTransfer->toArray(true, true),
        ], OrdersStorefrontResource::class);

        foreach ($resource->items as $index => $itemData) {
            $shipment = $orderTransfer->getItems()->offsetGet($index)?->getShipment();
            if ($shipment === null) {
                continue;
            }

            $resource->items[$index]['shipment'] = $shipment->toArray(true, true);
        }

        return $resource;
    }

    protected function hasSplitShipment(OrderTransfer $orderTransfer): bool
    {
        $shipmentIds = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $idSalesShipment = $itemTransfer->getShipment()?->getIdSalesShipment();

            if ($idSalesShipment !== null) {
                $shipmentIds[$idSalesShipment] = true;
            }
        }

        return count($shipmentIds) > 1;
    }
}
