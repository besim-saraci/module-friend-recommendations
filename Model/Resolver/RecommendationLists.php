<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationLists as RecommendationListsProvider;

class RecommendationLists implements ResolverInterface
{
    private RecommendationListsProvider $recommendationListsProvider;
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param RecommendationListsProvider $recommendationListsProvider
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        RecommendationListsProvider $recommendationListsProvider,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->recommendationListsProvider = $recommendationListsProvider;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {

        $contextExtAttributes = $context->getExtensionAttributes();

        /**
         * check if current user is a valid Customer
         */
        if (!$contextExtAttributes->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customerId = $context->getUserId();

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__('Customer doesn\'t exist.'));
        }

        return $this->recommendationListsProvider->getCustomerRecommendationLists($customer->getEmail());
    }
}
