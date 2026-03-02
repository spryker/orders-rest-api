<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\OrdersRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\OrdersRestApi\Dependency\Client\OrdersRestApiToSalesClientInterface;
use Spryker\Glue\OrdersRestApi\Processor\Expander\OrderByOrderReferenceResourceRelationshipExpander;
use Spryker\Glue\OrdersRestApi\Processor\Expander\OrderByOrderReferenceResourceRelationshipExpanderInterface;
use Spryker\Glue\OrdersRestApi\Processor\Expander\OrderItemExpander;
use Spryker\Glue\OrdersRestApi\Processor\Expander\OrderItemExpanderInterface;
use Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderMapper;
use Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderMapperInterface;
use Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderShipmentMapper;
use Spryker\Glue\OrdersRestApi\Processor\Mapper\OrderShipmentMapperInterface;
use Spryker\Glue\OrdersRestApi\Processor\Order\OrderReader;
use Spryker\Glue\OrdersRestApi\Processor\Order\OrderReaderInterface;
use Spryker\Glue\OrdersRestApi\Processor\RestResponseBuilder\OrderRestResponseBuilder;
use Spryker\Glue\OrdersRestApi\Processor\RestResponseBuilder\OrderRestResponseBuilderInterface;
use Spryker\Glue\OrdersRestApi\Processor\Validator\OrdersRestApiValidator;
use Spryker\Glue\OrdersRestApi\Processor\Validator\OrdersRestApiValidatorInterface;

/**
 * @method \Spryker\Glue\OrdersRestApi\OrdersRestApiConfig getConfig()
 */
class OrdersRestApiFactory extends AbstractFactory
{
    public function createOrderReader(): OrderReaderInterface
    {
        return new OrderReader(
            $this->getSalesClient(),
            $this->createOrderRestResponseBuilder(),
            $this->createOrdersRestApiValidator(),
        );
    }

    public function createOrderMapper(): OrderMapperInterface
    {
        return new OrderMapper(
            $this->createOrderShipmentMapper(),
            $this->getRestOrderItemsAttributesMapperPlugins(),
            $this->getRestOrderDetailsAttributesMapperPlugins(),
        );
    }

    public function createOrderShipmentMapper(): OrderShipmentMapperInterface
    {
        return new OrderShipmentMapper();
    }

    public function createOrderRestResponseBuilder(): OrderRestResponseBuilderInterface
    {
        return new OrderRestResponseBuilder(
            $this->getResourceBuilder(),
            $this->createOrderMapper(),
        );
    }

    public function createOrderItemExpander(): OrderItemExpanderInterface
    {
        return new OrderItemExpander(
            $this->getSalesClient(),
            $this->createOrderRestResponseBuilder(),
        );
    }

    public function createOrderByOrderReferenceResourceRelationshipExpander(): OrderByOrderReferenceResourceRelationshipExpanderInterface
    {
        return new OrderByOrderReferenceResourceRelationshipExpander($this->createOrderReader());
    }

    public function getSalesClient(): OrdersRestApiToSalesClientInterface
    {
        return $this->getProvidedDependency(OrdersRestApiDependencyProvider::CLIENT_SALES);
    }

    /**
     * @return array<\Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderItemsAttributesMapperPluginInterface>
     */
    public function getRestOrderItemsAttributesMapperPlugins(): array
    {
        return $this->getProvidedDependency(OrdersRestApiDependencyProvider::PLUGINS_REST_ORDER_ITEMS_ATTRIBUTES_MAPPER);
    }

    /**
     * @return array<\Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderDetailsAttributesMapperPluginInterface>
     */
    public function getRestOrderDetailsAttributesMapperPlugins(): array
    {
        return $this->getProvidedDependency(OrdersRestApiDependencyProvider::PLUGINS_REST_ORDER_DETAILS_ATTRIBUTES_MAPPER);
    }

    public function createOrdersRestApiValidator(): OrdersRestApiValidatorInterface
    {
        return new OrdersRestApiValidator();
    }
}
