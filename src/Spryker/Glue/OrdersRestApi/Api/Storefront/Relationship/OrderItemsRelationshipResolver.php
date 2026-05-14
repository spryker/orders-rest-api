<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\OrdersRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\OrderItemsStorefrontResource;
use Generated\Api\Storefront\OrdersStorefrontResource;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class OrderItemsRelationshipResolver extends AbstractRelationshipResolver implements PerItemRelationshipResolverInterface
{
    protected const string KEY_UUID = 'uuid';

    public function __construct(protected SerializerServiceInterface $serializer)
    {
    }

    /**
     * @return array<\Generated\Api\Storefront\OrderItemsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $allItems = [];

        /** @var array<\Generated\Api\Storefront\OrdersStorefrontResource> $parentResources */
        $parentResources = $this->getParentResources();

        foreach ($this->resolvePerItem($parentResources, $this->context) as $items) {
            array_push($allItems, ...$items);
        }

        return $allItems;
    }

    /**
     * @param array<\Generated\Api\Storefront\OrdersStorefrontResource> $parentResources
     *
     * @return array<string, array<\Generated\Api\Storefront\OrderItemsStorefrontResource>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $result = [];

        foreach ($parentResources as $parent) {
            $result[$parent->orderReference] = $this->buildItemsFromOrder($parent);
        }

        return $result;
    }

    /**
     * @return array<\Generated\Api\Storefront\OrderItemsStorefrontResource>
     */
    protected function buildItemsFromOrder(OrdersStorefrontResource $parent): array
    {
        $resources = [];

        foreach ($parent->items as $item) {
            if (!is_array($item) || !isset($item[static::KEY_UUID])) {
                continue;
            }

            $resources[] = $this->serializer->denormalize($item, OrderItemsStorefrontResource::class);
        }

        return $resources;
    }
}
