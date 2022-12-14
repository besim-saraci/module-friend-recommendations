<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\RecommendationLists;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationLists\Products as ProductsProvider;

class Products implements ResolverInterface
{
    private ValueFactory $valueFactory;
    private ProductsProvider $productsProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param ProductsProvider $productsProvider
     */
    public function __construct(
        ValueFactory     $valueFactory,
        ProductsProvider $productsProvider
    ) {
        $this->valueFactory = $valueFactory;
        $this->productsProvider = $productsProvider;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        if (!isset($value['id'])) {
            return [];
        }

        return $this->valueFactory->create(function () use ($value) {
            $allListProducts = $this->productsProvider->getAllRecListProducts($value['id']);
            foreach ($allListProducts as $listKey => $allListProduct) {
                if ($value['id'] == $listKey) {
                    return $allListProduct;
                }
            }
            return [];
        });
    }
}
