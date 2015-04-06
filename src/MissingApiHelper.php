<?php
namespace Mac2000\WooCommerceApiClient;
use BadMethodCallException;
use HieuLe\WordpressXmlrpcClient\WordpressClient;

/**
 * Class MissingApiHelper
 *
 * This is a helper class that realizes some missing WooCommerce API via XMLRPC
 *
 * @package Mac2000\WooCommerceApiClient
 */
class MissingApiHelper extends WordpressClient {
    const PRODUCT_CATEGORY = 'product_cat';

    // <editor-fold desc="Product categories">
    /**
     * Retrieve available product categories
     *
     * https://codex.wordpress.org/XML-RPC_WordPress_API/Taxonomies#wp.getTerms
     *
     * @return array
     */
    public function getCategories() {
        return $this->getTerms(self::PRODUCT_CATEGORY);
    }

    /**
     * Retrieve product category by its name
     *
     * https://codex.wordpress.org/XML-RPC_WordPress_API/Taxonomies#wp.getTerm
     *
     * @param string $name human readable category name, e.g. "T-Shirts"
     * @param null|int $parent_id optional id of parent category
     * @return array|null
     */
    public function getCategory($name, $parent_id = null) {
        $terms = $this->getCategories();

        $categories = array_filter($terms, function($term) use($name, $parent_id){
            return $parent_id
                ? strtolower($term['name']) == strtolower($name) && $term['parent'] == $parent_id
                : strtolower($term['name']) == strtolower($name);
        });

        return count($categories) > 0 ? array_shift($categories) : null;
    }

    /**
     * Check does category exists or not
     *
     * @param string $name human readable category name, e.g. "T-Shirts"
     * @param null|int $parent_id optional id of parent category
     * @return bool
     */
    public function hasCategory($name, $parent_id = null) {
        return $this->getCategory($name, $parent_id) ? true : false;
    }

    /**
     * Create new product category
     *
     * https://codex.wordpress.org/XML-RPC_WordPress_API/Taxonomies#wp.newTerm
     *
     * @param string $name human readable category name, e.g. "T-Shirts"
     * @param null|int $parent_id optional id of parent category
     * @return array|null
     */
    public function newCategory($name, $parent_id = null) {
        $this->newTerm($name, self::PRODUCT_CATEGORY, null, null, $parent_id);
        return $this->getCategory($name, $parent_id);
    }

    /**
     * Ensure that category exists
     *
     * @param string $name human readable category name, e.g. "T-Shirts"
     * @param null|int $parent_id optional id of parent category
     * @return array|null
     */
    public function ensureCategory($name, $parent_id = null) {
        $term = $this->getCategory($name, $parent_id);

        if(!$term) {
            $this->newCategory($name, $parent_id);
            $term = $this->getCategory($name, $parent_id);
        }

        return $term;
    }

    /**
     * Ensure two level category existence
     *
     * @param string $parentCategoryName human readable name for parent category, e.g. "Wear"
     * @param string $childCategoryName human readable name for child category, e.g. "T-Shirts"
     * @return array
     */
    public function ensureTwoLevelProductCategory($parentCategoryName, $childCategoryName) {
        $parentTerm = $this->ensureCategory($parentCategoryName);
        $childTerm = $this->ensureCategory($childCategoryName, $parentTerm['term_id']);

        return [
            $parentTerm,
            $childTerm
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Product attributes">

    /**
     * Get attribute definition by its name
     *
     * https://codex.wordpress.org/XML-RPC_WordPress_API/Taxonomies#wp.getTaxonomy
     *
     * @param string $name human readable name for product attribute, e.g. "Brand"
     * @return array|null
     */
    public function getAttribute($name) {
        $taxonomies = $this->getTaxonomies();

        $attributes = array_filter($taxonomies, function($taxonomy) use($name){
            return $taxonomy['label'] == $name && strpos($taxonomy['name'], 'pa_') === 0;
        });

        return count($attributes) > 0 ? array_shift($attributes) : null;
    }

    /**
     * Check whether attribute exists or not
     *
     * @param string $name human readable name for product attribute, e.g. "Brand"
     * @return bool
     */
    public function hasAttribute($name) {
        return $this->getAttribute($name) ? true : false;
    }

    /**
     * Unfortunately at this moment there is no way to create new attributes from outside
     * so you should create them by hand
     *
     * @param string $name human readable name for product attribute, e.g. "Brand"
     * @throws BadMethodCallException
     */
    public function newAttribute($name) {
        throw new BadMethodCallException('New attributes can not be created from outside');
    }

    /**
     * Get attribute option
     *
     * https://codex.wordpress.org/XML-RPC_WordPress_API/Taxonomies#wp.getTerm
     *
     * @param string $attributeName human readable attribute name, e.g. "Brand"
     * @param string $optionName human readable option name, e.g. "Nike"
     * @return array|null
     */
    public function getAttributeOption($attributeName, $optionName) {
        $taxonomy = $this->getAttribute($attributeName);
        $terms = $this->getTerms($taxonomy['name']);
        $filtered = array_filter($terms, function($term) use($optionName) {
            return strtolower($term['name']) == strtolower($optionName);
        });

        return count($filtered) > 0 ? array_shift($filtered) : null;
    }

    /**
     * Check does attribute option exists or not
     *
     * @param string $attributeName human readable attribute name, e.g. "Brand"
     * @param string $optionName human readable option name, e.g. "Nike"
     * @return bool
     */
    public function hasAttributeOption($attributeName, $optionName) {
        return $this->getAttributeOption($attributeName, $optionName) ? true : false;
    }

    /**
     * Create new attribute option
     *
     * https://codex.wordpress.org/XML-RPC_WordPress_API/Taxonomies#wp.newTerm
     *
     * @param string $attributeName human readable attribute name, e.g. "Brand"
     * @param string $optionName human readable option name, e.g. "Nike"
     * @return array|null
     */
    public function newAttributeOption($attributeName, $optionName) {
        $taxonomy = $this->getAttribute($attributeName);
        $this->newTerm($optionName, $taxonomy['name']);
        return $this->getAttributeOption($attributeName, $optionName);
    }

    /**
     * Ensure existence of attribute option
     *
     * @param string $attributeName human readable attribute name, e.g. "Brand"
     * @param string $optionName human readable option name, e.g. "Nike"
     * @return array|null
     */
    public function ensureAttributeOption($attributeName, $optionName) {
        $term = $this->getAttributeOption($attributeName, $optionName);

        if(!$term) {
            $this->newAttributeOption($attributeName, $optionName);
            $term = $this->getAttributeOption($attributeName, $optionName);
        }

        return $term;
    }
    // </editor-fold>
}