<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\OrdersRestApi\Processor\RestResponseBuilder;

use ArrayObject;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\RestOrderItemsAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestLinkInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\OrdersRestApi\OrdersRestApiConfig;
use Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderMapperInterface;
use Symfony\Component\HttpFoundation\Response;

class OrderRestResponseBuilder implements OrderRestResponseBuilderInterface
{
    protected const FORMAT_SELF_LINK_ORDER_ITEMS_RESOURCE = '%s/%s/%s/%s';

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderMapperInterface
     */
    protected $orderResourceMapper;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderMapperInterface $orderResourceMapper
     */
    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        OrderMapperInterface $orderResourceMapper
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->orderResourceMapper = $orderResourceMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    public function createOrderRestResource(OrderTransfer $orderTransfer): RestResourceInterface
    {
        $restOrderDetailsAttributesTransfer = $this->orderResourceMapper
            ->mapOrderTransferToRestOrderDetailsAttributesTransfer($orderTransfer);

        $restResource = $this->restResourceBuilder->createRestResource(
            OrdersRestApiConfig::RESOURCE_ORDERS,
            $orderTransfer->getOrderReference(),
            $restOrderDetailsAttributesTransfer
        );

        return $restResource;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createOrderRestResponse(OrderTransfer $orderTransfer): RestResponseInterface
    {
        $orderRestResource = $this->createOrderRestResource($orderTransfer);

        return $this->restResourceBuilder->createRestResponse()->addResource($orderRestResource);
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\OrderTransfer[] $orderTransfers
     * @param int $totalItems
     * @param int $limit
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createOrderListRestResponse(ArrayObject $orderTransfers, int $totalItems, int $limit): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse($totalItems, $limit);

        foreach ($orderTransfers as $orderTransfer) {
            $restOrdersAttributesTransfer = $this->orderResourceMapper
                ->mapOrderTransferToRestOrdersAttributesTransfer($orderTransfer);

            $restResponse = $restResponse->addResource(
                $this->restResourceBuilder->createRestResource(
                    OrdersRestApiConfig::RESOURCE_ORDERS,
                    $orderTransfer->getOrderReference(),
                    $restOrdersAttributesTransfer
                )
            );
        }

        return $restResponse;
    }

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createOrderNotFoundErrorResponse(): RestResponseInterface
    {
        $restErrorTransfer = (new RestErrorMessageTransfer())
            ->setCode(OrdersRestApiConfig::RESPONSE_CODE_CANT_FIND_ORDER)
            ->setStatus(Response::HTTP_NOT_FOUND)
            ->setDetail(OrdersRestApiConfig::RESPONSE_DETAIL_CANT_FIND_ORDER);

        return $this->restResourceBuilder->createRestResponse()->addError($restErrorTransfer);
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface[]
     */
    public function createMappedOrderItemRestResourcesFromOrderItemTransfers(ArrayObject $itemTransfers): array
    {
        $restResources = [];

        foreach ($itemTransfers as $itemTransfer) {
            $restResources[$itemTransfer->getUuid()] = $this->createOrderItemRestResource($itemTransfer);
        }

        return $restResources;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    protected function createOrderItemRestResource(ItemTransfer $itemTransfer): RestResourceInterface
    {
        $restOrderItemsAttributesTransfer = $this->orderResourceMapper
            ->mapItemTransferToRestOrderItemsAttributesTransfer($itemTransfer, new RestOrderItemsAttributesTransfer());

        $orderItemResource = $this->restResourceBuilder->createRestResource(
            OrdersRestApiConfig::RESOURCE_ORDER_ITEMS,
            $itemTransfer->getUuid(),
            $restOrderItemsAttributesTransfer
        );

        $orderItemResource->addLink(
            RestLinkInterface::LINK_SELF,
            $this->createSelfLinkForOrderItem($itemTransfer->getOrderReference(), $itemTransfer->getUuid())
        );

        return $orderItemResource;
    }

    /**
     * @param string $idOrder
     * @param string $idOrderItem
     *
     * @return string
     */
    protected function createSelfLinkForOrderItem(string $idOrder, string $idOrderItem): string
    {
        return sprintf(
            static::FORMAT_SELF_LINK_ORDER_ITEMS_RESOURCE,
            OrdersRestApiConfig::RESOURCE_ORDERS,
            $idOrder,
            OrdersRestApiConfig::RESOURCE_ORDER_ITEMS,
            $idOrderItem
        );
    }
}
