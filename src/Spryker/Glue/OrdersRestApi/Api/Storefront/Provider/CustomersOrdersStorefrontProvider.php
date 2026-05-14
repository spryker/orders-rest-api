<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CustomersOrdersStorefrontResource;
use Generated\Shared\Transfer\OrderListRequestTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Sales\SalesClientInterface;
use Spryker\Glue\OrdersRestApi\Api\Storefront\Exception\OrdersExceptionFactoryInterface;
use Spryker\Glue\OrdersRestApi\Api\Storefront\Mapper\OrderStorefrontMapperInterface;

class CustomersOrdersStorefrontProvider extends AbstractStorefrontProvider
{
    public function __construct(
        protected SalesClientInterface $salesClient,
        protected OrdersExceptionFactoryInterface $exceptionFactory,
        protected OrderStorefrontMapperInterface $orderStorefrontMapper,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\CustomersOrdersStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $request = $this->getRequest();

        // CustomerRequestSubscriber overwrites request.attributes.customerReference with the
        // authenticated value, which also propagates to $uriVariables. Use _route_params
        // to get the original URI value for ownership comparison.
        $routeParams = $request->attributes->get('_route_params', []);
        $customerReference = $routeParams['customerReference'] ?? null;

        if ($customerReference === null || $customerReference === '') {
            throw $this->exceptionFactory->createUnauthorizedCustomerException();
        }

        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createMissingAccessTokenException();
        }

        $authenticatedCustomerReference = $this->getCustomerReference();

        if ($authenticatedCustomerReference !== $customerReference) {
            throw $this->exceptionFactory->createUnauthorizedCustomerException();
        }

        $filterTransfer = $this->buildFilterTransfer();
        $orderListRequestTransfer = (new OrderListRequestTransfer())
            ->setCustomerReference($customerReference)
            ->setFilter($filterTransfer);

        $orderListTransfer = $this->salesClient->getOffsetPaginatedCustomerOrderList($orderListRequestTransfer);

        $resources = [];

        foreach ($orderListTransfer->getOrders() as $orderTransfer) {
            $resources[] = $this->orderStorefrontMapper->mapOrderTransferToCustomersOrdersResource(
                $orderTransfer,
                new CustomersOrdersStorefrontResource(),
            );
        }

        $pagination = $orderListTransfer->getPagination();

        if ($pagination !== null && count($resources) > 0) {
            $nbResults = $pagination->getNbResults() ?? 0;
            $resources[0]->pagination = $this->calculatePagination($filterTransfer->getOffsetOrFail(), $filterTransfer->getLimitOrFail(), $nbResults);
        }

        return $resources;
    }
}
