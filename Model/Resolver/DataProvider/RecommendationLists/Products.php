<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationLists;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Products
{
    private array $recListProducts = [];
    private array $uniqueProducts = [];

    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private ProductRepositoryInterface $productRepository;
    private ImageHelper $imageHelper;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        SearchCriteriaBuilder                        $searchCriteriaBuilder,
        ProductRepositoryInterface                   $productRepository,
        ImageHelper $imageHelper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param int $listId
     * @return array
     */
    public function getAllRecListProducts(int $listId): array
    {
        if (!$listId) {
            return [];
        }

        // check if products are already loaded for current list
        if (!isset($this->recListProducts[$listId])) {
            $allRecListProducts = $this->getFilteredRecListProducts($listId);

            foreach ($allRecListProducts as $allRecListProduct) {

                /**
                 * since same product can be in different list, store them in a unique table
                 */
                if (!in_array($allRecListProduct['sku'], array_keys($this->uniqueProducts))) {
                    $this->uniqueProducts[$allRecListProduct['sku']] = $this->formatRecListProductData($listId, $allRecListProduct);
                }
                $this->recListProducts[$listId][] = $this->uniqueProducts[$allRecListProduct['sku']];
            }
        }

        return $this->recListProducts;
    }

    /**
     * @param int $listId
     * @return array
     */
    private function getFilteredRecListProducts(int $listId): array
    {
        $this->searchCriteriaBuilder->addFilter('recommendation_list_ids', $listId, 'eq');
        return $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param int $listId
     * @param ProductInterface $product
     * @return array
     */
    private function formatRecListProductData(int $listId, ProductInterface $product): array
    {
        return [
            'name' => $product['name'],
            'sku' => $product['sku'],
            'thumbnailUrl' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl()
        ];
    }
}
