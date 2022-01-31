<?php
declare(strict_types=1);

namespace Codilar\ProductsGraphql\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Product collection resolver
 */
class Products implements ResolverInterface
{
    /**
     * SortOrder builder
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CollectionFactory $collectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->productCollection = $collectionFactory;

    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $productsData = $this->getProductsData();
        return $productsData;
    }

    /**
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getProductsData(): array
    {
        try {
            $collection = $this->productCollection->create()->addFieldToSelect('*')
            ->setFlag('has_stock_status_filter', false)
            ->joinField('stock_item', 'cataloginventory_stock_item', 'qty','product_id=entity_id', 'qty>=100');
            $productRecord['allProducts'] = [];
            foreach($collection as $product) {
                $productId = $product->getId();
                $productRecord['allProducts'][$productId]['sku'] = $product->getSku();
                $productRecord['allProducts'][$productId]['name'] = $product->getName();
                $productRecord['allProducts'][$productId]['price'] = $product->getPrice();
                }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $productRecord;
    }
}