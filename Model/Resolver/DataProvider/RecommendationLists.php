<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class RecommendationLists
{
    private RecommendationListRepositoryInterface $recommendationListRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param RecommendationListRepositoryInterface $recommendationListRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RecommendationListRepositoryInterface $recommendationListRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->recommendationListRepository = $recommendationListRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @throws GraphQlNoSuchEntityException
     */
    public function getCustomerRecommendationLists(string $customerEmail): array
    {
        $this->searchCriteriaBuilder->addFilter('email', $customerEmail);
        $recLists = $this->recommendationListRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        if (empty($recLists)) {
            throw new GraphQlNoSuchEntityException(__('No recommendation lists for this customer.'));
        }

        return $this->formatListsData($recLists);
    }

    /**
     * @param RecommendationListInterface[] $recLists
     * @return array
     */
    private function formatListsData(array $recLists): array
    {
        $formattedLists = [];

        foreach ($recLists as $recList) {

            /**
             * format recommendation list data in array format
             */
            $formattedLists [] =  [
                'id' => (int) $recList->getId(),
                'friendName' => $recList->getFriendName(),
                'title' => $recList->getTitle(),
                'note' => $recList->getNote()
            ];
        }

        return $formattedLists;
    }
}
