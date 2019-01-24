<?php

namespace XFApi\Domain\DBTech\eCommerce;

use XFApi\Dto\DBTech\eCommerce\ProductDto;
use XFApi\Dto\DBTech\eCommerce\ProductsDto;

/**
 * Class ProductDomain
 *
 * @package XFApi\Domain\DBTech\eCommerce
 */
class ProductDomain extends AbstracteCommerceDomain
{
    /**
     * @param int $page
     *
     * @return \XFApi\Dto\AbstractPaginatedDto
     * @throws \XFApi\Exception\XFApiException
     */
    public function getProducts($page = 1)
    {
        $uri = $this->getUri('');
        $products = $this->get($uri, ['page' => $page]);

        return $this->getPaginatedDto(ProductsDto::class, $products['products'], $products['pagination']);
    }
    
    /**
     * @param $productId
     *
     * @return \XFApi\Dto\AbstractItemDto
     * @throws \XFApi\Exception\XFApiException
     */
    public function getProduct($productId)
    {
        $uri = $this->getUri(null, ['product_id' => $productId]);
        $product = $this->get($uri);
        return $this->getDto(ProductDto::class, $product['product']);
    }
    
    /**
     * @param null $uri
     * @param array $params
     *
     * @return string
     */
    protected function getUri($uri = null, array $params = [])
    {
        $return = 'dbtech-ecommerce';
        if (isset($params['product_id'])) {
            $return .= '/' . $params['product_id'];
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
        return ProductDto::class;
    }
}
