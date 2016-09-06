<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\trigger;

use core\Route;
use core\classes\Tool;
use core\models\User as ModelsUser;
use core\models\Product as ModelsProduct;
use core\models\Currency as ModelsCurrency;
use core\models\Condition as ModelsCondition;

class Condition
{

    /**
     * Route class instance
     * @var \core\Route $route
     */
    protected $route;

    /**
     * Condition model instance
     * @var \core\models\Condition $condition
     */
    protected $condition;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param ModelsCondition $condition
     * @param ModelsUser $user
     * @param ModelsCurrency $currency
     * @param ModelsProduct $product
     * @param Route $route
     */
    public function __construct(ModelsCondition $condition, ModelsUser $user,
            ModelsCurrency $currency, ModelsProduct $product, Route $route)
    {
        $this->route = $route;
        $this->user = $user;
        $this->condition = $condition;
        $this->product = $product;
        $this->currency = $currency;
    }

    /**
     * Returns true if route condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function route(array $condition, array $data)
    {
        $patterns = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $route = $this->route->getCurrent();
        return $this->condition->compareString($route['pattern'], $patterns, $condition['operator']);
    }

    /**
     * Returns true if path condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function path(array $condition, array $data)
    {
        $patterns = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $path = $this->route->path();

        $found = false;
        foreach ($patterns as $pattern) {
            if (Tool::patternMatch($path, $pattern)) {
                $found = true;
            }
        }

        return ($condition['operator'] === '=') ? $found : !$found;
    }

    /**
     * Returns true if a date condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function date(array $condition, array $data)
    {
        $condition_value = reset($condition['value']);
        return $this->condition->compareNumeric(GC_TIME, (int) $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a number of usage condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function used(array $condition, array $data)
    {
        if (!isset($data['rule']['used'])) {
            return false;
        }

        $condition_value = reset($condition['value']);
        return $this->condition->compareNumeric((int) $data['rule']['used'], (int) $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a cart total condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function cartTotal(array $condition, array $data)
    {
        if (!isset($data['cart']['total']) || empty($data['cart']['currency'])) {
            return false;
        }

        $condition_value = explode('|', reset($condition['value']));
        $cart_currency = $data['cart']['currency'];
        $cart_subtotal = (int) $data['cart']['total'];
        $condition_currency = $cart_currency;
        $condition_operator = $condition['operator'];

        if (!empty($condition_value[1])) {
            $condition_currency = $condition_value[1];
        }

        $condition_price = $this->currency->convert((int) $condition_value[0], $condition_currency, $cart_currency);
        return $this->condition->compareNumeric($cart_subtotal, $condition_price, $condition_operator);
    }

    /**
     * Returns true if a product ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function productId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        foreach ($data['cart']['items'] as $item) {
            if ($this->condition->compareNumeric((int) $item['product_id'], $condition_value, $condition['operator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product category ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function categoryId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        if (empty($products)) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        foreach ($products as $product) {
            if ($this->condition->compareNumeric((int) $product['category_id'], $condition_value, $condition['operator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product brand condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function brandCategoryId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        if (empty($products)) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        foreach ($products as $product) {
            $match = $this->condition->compareNumeric((int) $product['brand_category_id'], $condition_value, $condition['operator']);

            if ($match) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a user ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function userId(array $condition, array $data)
    {
        $user_id = $this->user->id();

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->condition->compareNumeric($user_id, $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a user role condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function userRole(array $condition, array $data)
    {
        $role_id = $this->user->roleId();

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->condition->compareNumeric($role_id, $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a shipping service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shipping(array $condition, array $data)
    {
        if (!isset($data['data']['order']['shipping'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->condition->compareString($data['data']['order']['shipping'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a payment service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function payment(array $condition, array $data)
    {
        if (!isset($data['data']['order']['payment'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->condition->compareString($data['data']['order']['payment'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a shipping address condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shippingAddressId(array $condition, array $data)
    {
        if (empty($data['data']['order']['shipping_address'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];
        $address_id = $data['data']['order']['shipping_address'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->condition->compareNumeric($address_id, $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a country condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function country(array $condition, array $data)
    {
        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        if (isset($data['data']['address']['country'])) {
            $country = $data['data']['address']['country'];
            return $this->condition->compareString($country, $condition_value, $condition['operator']);
        }

        if (!isset($data['data']['order']['shipping_address'])) {
            return false;
        }

        $address_id = $data['data']['order']['shipping_address'];
        $address = $this->address->get($address_id);

        if (empty($address['country'])) {
            return false;
        }

        return $this->condition->compareString($address['country'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a state condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function state(array $condition, array $data)
    {
        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        if (isset($data['data']['address']['state_id'])) {
            $country = $data['data']['address']['state_id'];
            return $this->condition->compareNumeric($country, $condition_value, $condition['operator']);
        }

        if (!isset($data['data']['order']['shipping_address'])) {
            return false;
        }

        $address_id = $data['data']['order']['shipping_address'];
        $address = $this->address->get($address_id);

        if (empty($address['state_id'])) {
            return false;
        }

        return $this->condition->compareNumeric($address['state_id'], $condition_value, $condition['operator']);
    }

}