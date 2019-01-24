<?php

namespace XFApi\Domain\DBTech\eCommerce;

use XFApi\Dto\DBTech\eCommerce\ProductReviewDto;
use XFApi\Dto\DBTech\eCommerce\ProductReviewsDto;

/**
 * Class ProductReviewDomain
 *
 * @package XFApi\Domain\DBTech\eCommerce
 */
class ProductReviewDomain extends AbstracteCommerceDomain
{
    /**
     * @param int $page
     *
     * @return \XFApi\Dto\AbstractPaginatedDto
     * @throws \XFApi\Exception\XFApiException
     */
    public function getReviews($page = 1)
    {
        $uri = $this->getUri('');
        $products = $this->get($uri, ['page' => $page]);

        return $this->getPaginatedDto(ProductReviewsDto::class, $products['products'], $products['pagination']);
    }
    
    /**
     * @param $productId
     *
     * @return \XFApi\Dto\AbstractItemDto
     * @throws \XFApi\Exception\XFApiException
     */
    public function getReview($productId)
    {
        $uri = $this->getUri(null, ['product_rating_id' => $productId]);
        $review = $this->get($uri);
        return $this->getDto(ProductReviewDto::class, $review['review']);
    }
    
    /**
     * @param null $uri
     * @param array $params
     *
     * @return string
     */
    protected function getUri($uri = null, array $params = [])
    {
        $return = 'dbtech-ecommerce/reviews';
        if (isset($params['product_rating_id'])) {
            $return .= '/' . $params['product_rating_id'];
        }

        if (!empty($uri)) {
            $return .= '/' . $uri;
        }

        return $return;
    }
    
    /**
     * @return string
     */
    protected function getDtoClass()
    {
        return ProductReviewsDto::class;
    }
}
