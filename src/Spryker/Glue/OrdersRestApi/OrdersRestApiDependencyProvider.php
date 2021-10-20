<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\OrdersRestApi;

use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;
use Spryker\Glue\OrdersRestApi\Dependency\Client\OrdersRestApiToSalesClientBridge;

/**
 * @method \Spryker\Glue\OrdersRestApi\OrdersRestApiConfig getConfig()
 */
class OrdersRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_SALES = 'CLIENT_SALES';

    /**
     * @var string
     */
    public const PLUGINS_REST_ORDER_ITEMS_ATTRIBUTES_MAPPER = 'PLUGINS_REST_ORDER_ITEMS_ATTRIBUTES_MAPPER';

    /**
     * @var string
     */
    public const PLUGINS_REST_ORDER_DETAILS_ATTRIBUTES_MAPPER = 'PLUGINS_REST_ORDER_DETAILS_ATTRIBUTES_MAPPER';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addSalesClient($container);
        $container = $this->addRestOrderItemsAttributesMapperPlugins($container);
        $container = $this->addRestOrderDetailsAttributesMapperPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addSalesClient(Container $container): Container
    {
        $container->set(static::CLIENT_SALES, function (Container $container) {
            return new OrdersRestApiToSalesClientBridge($container->getLocator()->sales()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addRestOrderItemsAttributesMapperPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_REST_ORDER_ITEMS_ATTRIBUTES_MAPPER, function () {
            return $this->getRestOrderItemsAttributesMapperPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderItemsAttributesMapperPluginInterface>
     */
    protected function getRestOrderItemsAttributesMapperPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addRestOrderDetailsAttributesMapperPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_REST_ORDER_DETAILS_ATTRIBUTES_MAPPER, function () {
            return $this->getRestOrderDetailsAttributesMapperPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderDetailsAttributesMapperPluginInterface>
     */
    protected function getRestOrderDetailsAttributesMapperPlugins(): array
    {
        return [];
    }
}
