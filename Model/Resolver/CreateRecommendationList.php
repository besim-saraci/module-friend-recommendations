<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Model\Service\CreateRecommendationList as CreateRecommendationListService;

class CreateRecommendationList implements ResolverInterface
{
    private CreateRecommendationListService $createRecommendationListService;

    /**
     * @param CreateRecommendationListService $createRecommendationListService
     */
    public function __construct(
        CreateRecommendationListService $createRecommendationListService
    ) {
        $this->createRecommendationListService = $createRecommendationListService;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     * @throws GraphQlInputException
     * @throws CouldNotSaveException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!isset($args['email'])) {
            throw new GraphQlInputException(__('Email is required to create a recommendation list'));
        }
        if (!isset($args['friendName'])) {
            throw new GraphQlInputException(__('Friend name is required to create a recommendation list'));
        }

        return $this->createRecommendationListService->execute($args);
    }

}
