<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Service;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\Exception\CouldNotSaveException;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\RecommendationListProductRepositoryInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class CreateRecommendationList
{
    private RecommendationListRepositoryInterface $recommendationListRepository;
    private RecommendationListInterfaceFactory $recommendationListInterfaceFactory;
    private RecommendationListProductRepositoryInterface $recommendationListProductRepository;
    private RecommendationListProductInterfaceFactory $recommendationListProductInterfaceFactory;
    private ProductCollection $productCollection;

    /**
     * @param RecommendationListRepositoryInterface $recommendationListRepository
     * @param RecommendationListInterfaceFactory $recommendationListInterfaceFactory
     * @param RecommendationListProductRepositoryInterface $recommendationListProductRepository
     * @param RecommendationListProductInterfaceFactory $recommendationListProductInterfaceFactory
     * @param ProductCollection $productCollection
     */
    public function __construct(
        RecommendationListRepositoryInterface        $recommendationListRepository,
        RecommendationListInterfaceFactory           $recommendationListInterfaceFactory,
        RecommendationListProductRepositoryInterface $recommendationListProductRepository,
        RecommendationListProductInterfaceFactory    $recommendationListProductInterfaceFactory,
        ProductCollection                            $productCollection
    ) {
        $this->recommendationListRepository = $recommendationListRepository;
        $this->recommendationListInterfaceFactory = $recommendationListInterfaceFactory;
        $this->recommendationListProductRepository = $recommendationListProductRepository;
        $this->recommendationListProductInterfaceFactory = $recommendationListProductInterfaceFactory;
        $this->productCollection = $productCollection;
    }

    /**
     * @param array $args
     * @return array
     * @throws CouldNotSaveException
     */
    public function execute(array $args): array
    {
        /**
         * create recommendation list
         */
        $recommendationList = $this->recommendationListInterfaceFactory->create();
        $recommendationList->setEmail($args['email'])
            ->setFriendName($args['friendName'])
            ->setTitle($args['title'] ?? '')
            ->setNote($args['note'] ?? '');

        $createdRecList = $this->recommendationListRepository->save($recommendationList);

        /**
         * save recommendation list products
         */
        if (isset($args['productSkus']) && $createdRecList->getId()) {
            $productSkus = $this->validateProductSkus($args['productSkus']);
            $this->saveRecListProducts((int) $createdRecList->getId(), $productSkus);
        }

        return [
            'email' => $createdRecList->getEmail(),
            'friendName' => $createdRecList->getFriendName(),
            'title' => $createdRecList->getTitle(),
            'note' => $createdRecList->getNote()
        ];
    }

    /**
     * @param int $listId
     * @param array $skus
     * @return void
     * @throws CouldNotSaveException
     */
    private function saveRecListProducts(int $listId, array $skus): void
    {
        foreach ($skus as $productSku) {
            $recommendationListProduct = $this->recommendationListProductInterfaceFactory->create();
            $recommendationListProduct->setListId($listId)
                ->setSku($productSku);
            $this->recommendationListProductRepository->save($recommendationListProduct);
        }
    }

    /**
     * @param array $skus
     * @return array
     * return only valid skus and do not throw error for non-existing skus
     */
    private function validateProductSkus(array $skus): array
    {
        $productCollection = $this->productCollection->create();
        $items = $productCollection->addAttributeToSelect('sku')
            ->addFieldToFilter('sku', ['in' => $skus])
            ->getItems();

        $validItems = [];

        foreach ($items as $item) {
            $validItems[] = $item['sku'];
        }

        return $validItems;
    }
}
