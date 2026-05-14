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
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer;
use Generated\Shared\Transfer\RestOrderExpensesAttributesTransfer;
use Generated\Shared\Transfer\RestOrderItemsAttributesTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Spryker\Service\Container\Attributes\Plugins;

class OrderStorefrontMapper implements OrderStorefrontMapperInterface
{
    /**
     * @param array<\Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderItemsAttributesMapperPluginInterface> $restOrderItemsAttributesMapperPlugins
     * @param array<\Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderDetailsAttributesMapperPluginInterface> $restOrderDetailsAttributesMapperPlugins
     */
    public function __construct(
        #[Plugins(dependencyProviderMethod: 'getRestOrderItemsAttributesMapperPlugins')]
        protected array $restOrderItemsAttributesMapperPlugins = [],
        #[Plugins(dependencyProviderMethod: 'getRestOrderDetailsAttributesMapperPlugins')]
        protected array $restOrderDetailsAttributesMapperPlugins = [],
    ) {
    }

    public function mapItemTransferToOrderItemsStorefrontResource(
        ItemTransfer $itemTransfer,
        OrderItemsStorefrontResource $orderItemsStorefrontResource,
    ): OrderItemsStorefrontResource {
        $restOrderItemsAttributesTransfer = $this->mapItemTransferToRestOrderItemsAttributesTransfer(
            $itemTransfer,
            new RestOrderItemsAttributesTransfer(),
        );
        $itemData = $restOrderItemsAttributesTransfer->toArray(true, true);

        $orderItemsStorefrontResource->uuid = $itemTransfer->getUuid();
        $orderItemsStorefrontResource->name = $itemTransfer->getName();
        $orderItemsStorefrontResource->sku = $itemTransfer->getSku();
        $orderItemsStorefrontResource->quantity = $itemTransfer->getQuantity();
        $orderItemsStorefrontResource->sumPrice = $itemTransfer->getSumPrice();
        $orderItemsStorefrontResource->unitGrossPrice = $itemTransfer->getUnitGrossPrice();
        $orderItemsStorefrontResource->sumGrossPrice = $itemTransfer->getSumGrossPrice();
        $orderItemsStorefrontResource->taxRate = $itemTransfer->getTaxRate();
        $orderItemsStorefrontResource->unitNetPrice = $itemTransfer->getUnitNetPrice();
        $orderItemsStorefrontResource->sumNetPrice = $itemTransfer->getSumNetPrice();
        $orderItemsStorefrontResource->unitPrice = $itemTransfer->getUnitPrice();
        $orderItemsStorefrontResource->unitTaxAmountFullAggregation = $itemTransfer->getUnitTaxAmountFullAggregation();
        $orderItemsStorefrontResource->sumTaxAmountFullAggregation = $itemTransfer->getSumTaxAmountFullAggregation();
        $orderItemsStorefrontResource->refundableAmount = $itemTransfer->getRefundableAmount();
        $orderItemsStorefrontResource->canceledAmount = $itemTransfer->getCanceledAmount();
        $orderItemsStorefrontResource->sumSubtotalAggregation = $itemTransfer->getSumSubtotalAggregation();
        $orderItemsStorefrontResource->unitSubtotalAggregation = $itemTransfer->getUnitSubtotalAggregation();
        $orderItemsStorefrontResource->unitProductOptionPriceAggregation = $itemTransfer->getUnitProductOptionPriceAggregation();
        $orderItemsStorefrontResource->sumProductOptionPriceAggregation = $itemTransfer->getSumProductOptionPriceAggregation();
        $orderItemsStorefrontResource->unitExpensePriceAggregation = $itemTransfer->getUnitExpensePriceAggregation();
        $orderItemsStorefrontResource->sumExpensePriceAggregation = $itemTransfer->getSumExpensePriceAggregation();
        $orderItemsStorefrontResource->unitDiscountAmountAggregation = $itemTransfer->getUnitDiscountAmountAggregation();
        $orderItemsStorefrontResource->sumDiscountAmountAggregation = $itemTransfer->getSumDiscountAmountAggregation();
        $orderItemsStorefrontResource->unitDiscountAmountFullAggregation = $itemTransfer->getUnitDiscountAmountFullAggregation();
        $orderItemsStorefrontResource->sumDiscountAmountFullAggregation = $itemTransfer->getSumDiscountAmountFullAggregation();
        $orderItemsStorefrontResource->unitPriceToPayAggregation = $itemTransfer->getUnitPriceToPayAggregation();
        $orderItemsStorefrontResource->sumPriceToPayAggregation = $itemTransfer->getSumPriceToPayAggregation();
        $orderItemsStorefrontResource->taxRateAverageAggregation = $itemTransfer->getTaxRateAverageAggregation();
        $orderItemsStorefrontResource->taxAmountAfterCancellation = $itemTransfer->getTaxAmountAfterCancellation();
        $orderItemsStorefrontResource->orderReference = $itemTransfer->getOrderReference();
        $orderItemsStorefrontResource->isReturnable = $itemTransfer->getIsReturnable();
        $orderItemsStorefrontResource->idShipment = $itemTransfer->getShipment()?->getIdSalesShipment();
        $orderItemsStorefrontResource->metadata = is_array($itemData['metadata'] ?? null) ? $itemData['metadata'] : null;
        $orderItemsStorefrontResource->calculatedDiscounts = is_array($itemData['calculatedDiscounts'] ?? null)
            ? array_values($itemData['calculatedDiscounts'])
            : [];

        return $orderItemsStorefrontResource;
    }

    public function mapOrderTransferToCustomersOrdersResource(
        OrderTransfer $orderTransfer,
        CustomersOrdersStorefrontResource $customersOrdersStorefrontResource,
    ): CustomersOrdersStorefrontResource {
        $customersOrdersStorefrontResource->orderReference = $orderTransfer->getOrderReference();
        $customersOrdersStorefrontResource->createdAt = $orderTransfer->getCreatedAt();
        $customersOrdersStorefrontResource->currencyIsoCode = $orderTransfer->getCurrencyIsoCode();
        $customersOrdersStorefrontResource->priceMode = $orderTransfer->getPriceMode();
        $customersOrdersStorefrontResource->totals = $this->mapTotals($orderTransfer);

        return $customersOrdersStorefrontResource;
    }

    /**
     * @return array<string, mixed>
     */
    public function mapTotals(OrderTransfer $orderTransfer): array
    {
        $totals = $orderTransfer->getTotals();

        return [
            'expenseTotal' => $totals?->getExpenseTotal() ?? 0,
            'discountTotal' => $totals?->getDiscountTotal() ?? 0,
            'taxTotal' => $totals?->getTaxTotal()?->getAmount() ?? 0,
            'subtotal' => $totals?->getSubtotal() ?? 0,
            'grandTotal' => $totals?->getGrandTotal() ?? 0,
            'canceledTotal' => $totals?->getCanceledTotal() ?? 0,
            'remunerationTotal' => $totals?->getRemunerationTotal() ?? 0,
        ];
    }

    public function mapItemTransferToRestOrderItemsAttributesTransfer(
        ItemTransfer $itemTransfer,
        RestOrderItemsAttributesTransfer $restOrderItemsAttributesTransfer,
    ): RestOrderItemsAttributesTransfer {
        $restOrderItemsAttributesTransfer->fromArray($itemTransfer->toArray(), true);
        $restOrderItemsAttributesTransfer->setIdShipment($itemTransfer->getShipment()?->getIdSalesShipment());

        foreach ($this->restOrderItemsAttributesMapperPlugins as $plugin) {
            $restOrderItemsAttributesTransfer = $plugin->mapItemTransferToRestOrderItemsAttributesTransfer(
                $itemTransfer,
                $restOrderItemsAttributesTransfer,
            );
        }

        return $restOrderItemsAttributesTransfer;
    }

    public function mapOrderTransferToRestOrderDetailsAttributesTransfer(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer,
    ): RestOrderDetailsAttributesTransfer {
        $restItemTransfers = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $restItemTransfers[] = $this->mapItemTransferToRestOrderItemsAttributesTransfer(
                $itemTransfer,
                new RestOrderItemsAttributesTransfer(),
            );
        }

        $restOrderDetailsAttributesTransfer->setItems(new ArrayObject($restItemTransfers));

        foreach ($this->restOrderDetailsAttributesMapperPlugins as $plugin) {
            $restOrderDetailsAttributesTransfer = $plugin->mapOrderTransferToRestOrderDetailsAttributesTransfer(
                $orderTransfer,
                $restOrderDetailsAttributesTransfer,
            );
        }

        return $restOrderDetailsAttributesTransfer;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapOrderExpenses(OrderTransfer $orderTransfer): array
    {
        $expenses = [];

        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            $restExpenseAttributesTransfer = (new RestOrderExpensesAttributesTransfer())
                ->fromArray($expenseTransfer->toArray(), true);

            $restExpenseAttributesTransfer->setIdShipment($expenseTransfer->getShipment()?->getIdSalesShipment());

            $expenses[] = $restExpenseAttributesTransfer->toArray(true, true);
        }

        return $expenses;
    }

    /**
     * @return array<string, mixed>
     */
    public function mapBillingAddress(OrderTransfer $orderTransfer): array
    {
        $billingAddress = $orderTransfer->getBillingAddress();

        if ($billingAddress === null) {
            return [];
        }

        $data = $billingAddress->toArray(true, true);
        $country = $billingAddress->getCountry();

        if ($country !== null) {
            $data['country'] = $country->getName();
            $data['iso2Code'] = $country->getIso2Code();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function mapShippingAddress(OrderTransfer $orderTransfer): ?array
    {
        $shippingAddress = $orderTransfer->getShippingAddress();

        if ($shippingAddress === null) {
            return null;
        }

        $data = $shippingAddress->toArray(true, true);
        $countryTransfer = $this->findItemLevelShippingAddressCountry($orderTransfer);

        if ($countryTransfer !== null) {
            $data['country'] = $countryTransfer->getName();
            $data['iso2Code'] = $countryTransfer->getIso2Code();
        }

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapShipments(OrderTransfer $orderTransfer): array
    {
        $shipments = [];

        foreach ($orderTransfer->getShipmentMethods() as $shipmentMethodTransfer) {
            $shipments[] = $this->mapShipmentMethod($shipmentMethodTransfer, $orderTransfer->getExpenses(), $orderTransfer->getCurrencyIsoCode());
        }

        return $shipments;
    }

    /**
     * @param \ArrayObject<int, \Spryker\Shared\Kernel\Transfer\AbstractTransfer> $transferCollection
     *
     * @return array<int, array<string, mixed>>
     */
    public function convertTransferCollectionToArray(ArrayObject $transferCollection): array
    {
        $result = [];

        foreach ($transferCollection as $transfer) {
            $result[] = $transfer->toArray(true, true);
        }

        return $result;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ExpenseTransfer> $expenseTransfers
     *
     * @return array<string, mixed>
     */
    protected function mapShipmentMethod(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        ArrayObject $expenseTransfers,
        ?string $currencyIsoCode,
    ): array {
        $data = [
            'shipmentMethodName' => $shipmentMethodTransfer->getName(),
            'carrierName' => $shipmentMethodTransfer->getCarrierName(),
            'deliveryTime' => $shipmentMethodTransfer->getDeliveryTime(),
            'currencyIsoCode' => $currencyIsoCode,
            'defaultGrossPrice' => null,
            'defaultNetPrice' => null,
        ];

        foreach ($expenseTransfers as $expenseTransfer) {
            if ($expenseTransfer->getIdSalesExpense() !== $shipmentMethodTransfer->getFkSalesExpense()) {
                continue;
            }

            $data['defaultGrossPrice'] = $expenseTransfer->getSumGrossPrice();
            $data['defaultNetPrice'] = $expenseTransfer->getSumNetPrice();
        }

        return $data;
    }

    protected function findItemLevelShippingAddressCountry(OrderTransfer $orderTransfer): ?CountryTransfer
    {
        if ($orderTransfer->getItems()->count() === 0) {
            return null;
        }

        /** @var \Generated\Shared\Transfer\ItemTransfer $firstItemTransfer */
        $firstItemTransfer = $orderTransfer->getItems()->getIterator()->current();

        if ($firstItemTransfer->getShipment() === null || $firstItemTransfer->getShipment()->getShippingAddress() === null) {
            return null;
        }

        return $firstItemTransfer->getShipment()->getShippingAddress()->getCountry();
    }
}
