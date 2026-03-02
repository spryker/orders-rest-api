<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\OrdersRestApi\Processor\Order;

use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\OrderListRequestTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\OrdersRestApi\Dependency\Client\OrdersRestApiToSalesClientInterface;
use Spryker\Glue\OrdersRestApi\Processor\RestResponseBuilder\OrderRestResponseBuilderInterface;
use Spryker\Glue\OrdersRestApi\Processor\Validator\OrdersRestApiValidatorInterface;

class OrderReader implements OrderReaderInterface
{
    /**
     * @var \Spryker\Glue\OrdersRestApi\Dependency\Client\OrdersRestApiToSalesClientInterface
     */
    protected $salesClient;

    /**
     * @var \Spryker\Glue\OrdersRestApi\Processor\RestResponseBuilder\OrderRestResponseBuilderInterface
     */
    protected $orderRestResponseBuilder;

    /**
     * @var \Spryker\Glue\OrdersRestApi\Processor\Validator\OrdersRestApiValidatorInterface
     */
    protected $ordersRestApiValidator;

    public function __construct(
        OrdersRestApiToSalesClientInterface $salesClient,
        OrderRestResponseBuilderInterface $orderRestResponseBuilder,
        OrdersRestApiValidatorInterface $ordersRestApiValidator
    ) {
        $this->salesClient = $salesClient;
        $this->orderRestResponseBuilder = $orderRestResponseBuilder;
        $this->ordersRestApiValidator = $ordersRestApiValidator;
    }

    public function getOrderAttributes(RestRequestInterface $restRequest): RestResponseInterface
    {
        if ($restRequest->getResource()->getId()) {
            return $this->getOrderDetailsResourceAttributes(
                $restRequest->getResource()->getId(),
                $restRequest->getRestUser()->getNaturalIdentifier(),
            );
        }

        return $this->getOrderListAttributes($restRequest);
    }

    public function getCustomerOrders(RestRequestInterface $restRequest): RestResponseInterface
    {
        if (!$this->ordersRestApiValidator->isSameCustomerReference($restRequest)) {
            return $this->orderRestResponseBuilder->createCustomerUnauthorizedErrorResponse();
        }

        return $this->getOrderListAttributes($restRequest);
    }

    public function findCustomerOrder(string $orderReference, string $customerReference): ?RestResourceInterface
    {
        $orderTransfer = $this->findCustomerOrderTransfer($orderReference, $customerReference);

        if ($orderTransfer->getIdSalesOrder() === null) {
            return null;
        }

        return $this->orderRestResponseBuilder->createOrderRestResource($orderTransfer);
    }

    protected function findCustomerOrderTransfer(string $orderReference, string $customerReference): ?OrderTransfer
    {
        $orderTransfer = (new OrderTransfer())
            ->setOrderReference($orderReference)
            ->setCustomerReference($customerReference);
        $orderTransfer = $this->salesClient->getCustomerOrderByOrderReference($orderTransfer);

        if ($orderTransfer->getIdSalesOrder() === null) {
            return null;
        }

        return $orderTransfer;
    }

    protected function getOrderListAttributes(RestRequestInterface $restRequest): RestResponseInterface
    {
        $customerReference = $restRequest->getRestUser()->getNaturalIdentifier();
        $orderListRequestTransfer = (new OrderListRequestTransfer())->setCustomerReference($customerReference);

        $limit = 0;
        $filterTransfer = new FilterTransfer();

        if ($restRequest->getPage()) {
            $limit = $restRequest->getPage()->getLimit();
            $filterTransfer
                ->setOffset($restRequest->getPage()->getOffset())
                ->setLimit($restRequest->getPage()->getLimit());
        }
        $sortTransfer = $restRequest->getSort();

        if (isset($sortTransfer[0])) {
            $filterTransfer
                ->setOrderDirection($sortTransfer[0]->getDirection())
                ->setOrderBy($sortTransfer[0]->getField());
        }

        $orderListRequestTransfer->setFilter($filterTransfer);

        $orderListTransfer = $this->salesClient->getOffsetPaginatedCustomerOrderList($orderListRequestTransfer);

        $totalItems = $orderListTransfer->getPagination() ? $orderListTransfer->getPagination()->getNbResults() : 0;

        return $this->orderRestResponseBuilder->createOrderListRestResponse(
            $orderListTransfer->getOrders(),
            $totalItems,
            $limit,
        );
    }

    protected function getOrderDetailsResourceAttributes(string $orderReference, string $customerReference): RestResponseInterface
    {
        $orderTransfer = $this->findCustomerOrderTransfer($orderReference, $customerReference);

        if (!$orderTransfer) {
            return $this->orderRestResponseBuilder->createOrderNotFoundErrorResponse();
        }

        return $this->orderRestResponseBuilder->createOrderRestResponse($orderTransfer);
    }
}
