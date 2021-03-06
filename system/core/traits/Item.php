<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods for adding keys with different data to an item
 */
trait Item
{

    /**
     * @see \gplcart\core\Controller::getBase()
     */
    abstract public function getBase();

    /**
     * @see \gplcart\core\Controller::getStoreId()
     */
    abstract public function getStoreId();

    /**
     * @see \gplcart\core\Controller::getCartUid()
     */
    abstract public function getCartUid();

    /**
     * @see \gplcart\core\Controller::path()
     */
    abstract public function path($pattern = null);

    /**
     * @see \gplcart\core\Controller::config()
     */
    abstract public function config($key = null, $default = null);

    /**
     * @see \gplcart\core\Controller::configTheme()
     */
    abstract public function configTheme($key = null, $default = null);

    /**
     * @see \gplcart\core\Controller::getQuery()
     */
    abstract public function getQuery($key = null, $default = null, $type = 'string');

    /**
     * @see \gplcart\core\Controller::render()
     */
    abstract public function render($file, $data = array(), $merge = true, $default = '');

    /**
     * @see \gplcart\core\Controller::url()
     */
    abstract public function url($path = '', array $query = array(), $abs = false, $exclude = false);

    /**
     * Adds "thumb" key
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param array $options
     */
    public function setItemThumb(array &$item, $image_model, $options = array())
    {
        $options += array(
            'imagestyle' => $this->config('image_style', 3));

        if (!empty($options['path'])) {
            $item['thumb'] = $image_model->url($options['path'], $options['imagestyle']);
        } else if (!empty($item['path'])) {
            $item['thumb'] = $image_model->url($item['path'], $options['imagestyle']);
        } else if (empty($item['images'])) {
            $item['thumb'] = $image_model->getThumb($item, $options);
        } else {
            $first = reset($item['images']);
            $item['thumb'] = $image_model->url($first['path'], $options['imagestyle']);
            foreach ($item['images'] as &$image) {
                $image['url'] = $image_model->url($image['path']);
                $image['thumb'] = $image_model->url($image['path'], $options['imagestyle']);
                $this->setItemThumbIsPlaceholder($image, $image_model);
            }
        }

        $this->setItemThumbIsPlaceholder($item, $image_model);
    }

    /**
     * Adds "thumb_placeholder" key
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemThumbIsPlaceholder(array &$item, $image_model)
    {
        if (!empty($item['thumb'])) {
            $item['thumb_placeholder'] = $image_model->isPlaceholder($item['thumb']);
        }
    }

    /**
     * Add thumbs to the cart item
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemThumbCart(array &$item, $image_model)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $this->configTheme('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id']) && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        if (empty($options['path'])) {
            $item['thumb'] = $image_model->getPlaceholder($options['imagestyle']);
        } else {
            $this->setItemThumb($item, $image_model, $options);
        }
    }

    /**
     * Adds URL keys
     * @param array $item
     * @param array $options
     */
    public function setItemEntityUrl(array &$item, array $options = array())
    {
        if (!empty($options['entity']) && !empty($item[$options['entity'] . '_id'])) {

            $path = "{$options['entity']}/{$item["{$options['entity']}_id"]}";

            if (!empty($item['alias']) && $this->config('alias', true)) {
                $path = $item['alias'];
            }

            $item['url'] = $this->url($path);
            $item['url_query'] = $this->url($path, $this->getQuery(null, array(), 'array'));
        }
    }

    /**
     * Adds full store URL to the item
     * @param array $item
     * @param \gplcart\core\models\Store $store_model
     * @param string $entity
     */
    public function setItemUrlEntity(array &$item, $store_model, $entity = null)
    {
        if (!isset($entity)) {
            $entity = isset($item['entity']) ? $item['entity'] : null;
        }

        if (isset($entity) && isset($item['store_id']) && isset($item["{$entity}_id"])) {
            $store = $store_model->get($item['store_id']);
            if (!empty($store)) {
                $url = $store_model->url($store);
                $item['url'] = "$url/$entity/{$item["{$entity}_id"]}";
            }
        }
    }

    /**
     * Adds "rendered" key
     * @param array $item
     * @param array $data
     * @param array $options
     */
    public function setItemRendered(array &$item, $data, $options = array())
    {
        if (!empty($options['template_item'])) {
            $item['rendered'] = $this->render($options['template_item'], $data, true);
        }
    }

    /**
     * Add "active"
     * @param array $item
     */
    public function setItemUrlActive(array &$item)
    {
        if (isset($item['url'])) {
            $item['active'] = substr($item['url'], strlen($this->getBase())) === $this->path();
        }
    }

    /**
     * Add "indentation" key
     * @param array $item
     * @param string $char
     */
    public function setItemIndentation(array &$item, $char = '<span class="indentation"></span>')
    {
        if (isset($item['depth'])) {
            $item['indentation'] = str_repeat($char, $item['depth']);
        }
    }

    /**
     * Adds product thumb(s)
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemThumbProduct(array &$item, $image_model)
    {
        $options = array(
            'imagestyle' => $this->configTheme('image_style_product', 6));

        if (empty($item['images'])) {
            $item['images'][] = array(
                'title' => isset($item['title']) ? $item['title'] : '',
                'thumb' => $image_model->getPlaceholder($options['imagestyle']));
        } else {
            $this->setItemThumb($item, $image_model, $options);
        }
    }

    /**
     * Adds "in_comparison" key
     * @param array $item
     * @param \gplcart\core\models\ProductCompare $compare_model
     */
    public function setItemProductInComparison(array &$item, $compare_model)
    {
        $item['in_comparison'] = $compare_model->exists($item['product_id']);
    }

    /**
     * Adds "in_wishlist" key
     * @param array $item
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    public function setItemProductInWishlist(&$item, $wishlist_model)
    {
        $conditions = array(
            'user_id' => $this->getCartUid(),
            'store_id' => $this->getStoreId(),
            'product_id' => $item['product_id']
        );

        $item['in_wishlist'] = $wishlist_model->exists($conditions);
    }

    /**
     * Adds "rendered" key containing rendered product item
     * @param array $item
     * @param array $options
     */
    public function setItemRenderedProduct(array &$item, $options = array())
    {
        if (empty($options['template_item'])) {
            return null;
        }

        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'item' => $item,
            'buttons' => $options['buttons']
        );

        $this->setItemRendered($item, $data, $options);
    }

    /**
     * Adds "bundled_products" key
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     * @param \gplcart\core\models\Image $image_model
     * @param array $options
     */
    public function setItemProductBundle(&$item, $product_model, $image_model, $options = array())
    {
        if (empty($item['bundle'])) {
            return null;
        }

        $data = array(
            'status' => 1,
            'store_id' => $item['store_id'],
            'product_id' => explode(',', $item['bundle'])
        );

        $products = (array) $product_model->getList($data);

        $options += array(
            'entity' => 'product',
            'entity_id' => array_keys($products)
        );

        foreach ($products as &$product) {
            $this->setItemEntityUrl($product, $options);
            $this->setItemThumb($product, $image_model, $options);
            $this->setItemRenderedProductBundle($product, $options);
        }

        $item['bundled_products'] = $products;
    }

    /**
     * Sets rendered product bundled item
     * @param array $item
     * @param array $options
     */
    public function setItemRenderedProductBundle(array &$item, array $options = array())
    {
        $options += array(
            'template_item' => 'product/item/bundle');

        $this->setItemRendered($item, array('item' => $item), $options);
    }

    /**
     * Adds "fields" key
     * @param array $item
     * @param \gplcart\core\models\Image $imodel
     * @param \gplcart\core\models\ProductClass $pcmodel
     * @param string $type
     * @param array $options
     */
    public function setItemProductFieldType(&$item, $imodel, $pcmodel, $type, $options = [])
    {
        if (empty($item['field'][$type]) || empty($item['product_class_id'])) {
            return null;
        }

        $fields = $pcmodel->getFieldData($item['product_class_id']);

        foreach ($item['field'][$type] as $field_id => $field_values) {
            foreach ($field_values as $field_value_id) {

                $options += array(
                    'placeholder' => false,
                    'path' => $fields[$type][$field_id]['values'][$field_value_id]['path']
                );

                $this->setItemThumb($fields[$type][$field_id]['values'][$field_value_id], $imodel, $options);

                if (isset($fields[$type][$field_id]['values'][$field_value_id]['title'])) {
                    $item['field_value_labels'][$type][$field_id][$field_value_id] = $fields[$type][$field_id]['values'][$field_value_id]['title'];
                }
            }
        }

        $item['fields'][$type] = $fields[$type];
    }

    /**
     * Set a field data to the product item
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param \gplcart\core\models\ProductClass $class_model
     * @param array $options
     */
    public function setItemProductFields(&$item, $image_model, $class_model, $options = array())
    {
        $this->setItemProductFieldType($item, $image_model, $class_model, 'option', $options);
        $this->setItemProductFieldType($item, $image_model, $class_model, 'attribute', $options);
    }

    /**
     * Set a data to product combinations
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemProductCombination(array &$item, $image_model, $price_model)
    {
        if (empty($item['combination'])) {
            return null;
        }

        foreach ($item['combination'] as &$combination) {

            $combination['path'] = $combination['thumb'] = '';

            if (!empty($item['images'][$combination['file_id']])) {
                $combination['path'] = $item['images'][$combination['file_id']]['path'];
                $this->setItemThumb($combination, $image_model);
            }

            $combination['price'] = $price_model->decimal($combination['price'], $item['currency']);
        }
    }

    /**
     * Adds a cart component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemOrderCartComponent(&$item, $price_model)
    {
        if (empty($item['data']['components']['cart']['items'])) {
            return null;
        }

        foreach ($item['data']['components']['cart']['items'] as $sku => $component) {

            if (empty($item['cart'][$sku]['product_store_id'])) {
                continue;
            }

            if ($item['cart'][$sku]['product_store_id'] != $item['store_id']) {
                $item['cart'][$sku]['product_status'] = 0;
            }

            $item['cart'][$sku]['price_formatted'] = $price_model->format($component['price'], $item['currency']);
        }

        $html = $this->render('backend|sale/order/panes/components/cart', array('order' => $item));
        $item['data']['components']['cart']['rendered'] = $html;
    }

    /**
     * Adds a shipping component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Shipping $shmodel
     * @param \gplcart\core\models\Order $omodel
     */
    public function setItemOrderShippingComponent(&$item, $pmodel, $shmodel, $omodel)
    {
        if (!isset($item['data']['components']['shipping']['price'])) {
            return null;
        }

        $method = $shmodel->get($item['shipping']);
        $value = $item['data']['components']['shipping']['price'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $method['price_formatted'] = $pmodel->format($value, $item['currency']);

        $data = array(
            'method' => $method,
            'title' => $omodel->getComponentType('shipping')
        );

        $html = $this->render('backend|sale/order/panes/components/method', $data);
        $item['data']['components']['shipping']['rendered'] = $html;
    }

    /**
     * Adds a payment component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Payment $pamodel
     * @param \gplcart\core\models\Order $omodel
     */
    public function setItemOrderPaymentComponent(&$item, $pmodel, $pamodel, $omodel)
    {
        if (!isset($item['data']['components']['payment']['price'])) {
            return null;
        }

        $method = $pamodel->get($item['payment']);
        $value = $item['data']['components']['payment']['price'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $method['price_formatted'] = $pmodel->format($value, $item['currency']);

        $data = array(
            'method' => $method,
            'title' => $omodel->getComponentType('payment')
        );

        $html = $this->render('backend|sale/order/panes/components/method', $data);
        $item['data']['components']['payment']['rendered'] = $html;
    }

    /**
     * Adds a price rule component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\PriceRule $prmodel
     */
    public function setItemOrderPriceRuleComponent(&$item, $pmodel, $prmodel)
    {
        foreach ($item['data']['components'] as $price_rule_id => $component) {

            if (!is_numeric($price_rule_id)) {
                continue;
            }

            $price_rule = $prmodel->get($price_rule_id);

            if (abs($component['price']) == 0) {
                $component['price'] = 0;
            }

            $data = array(
                'rule' => $price_rule,
                'price' => $pmodel->format($component['price'], $price_rule['currency']));

            $html = $this->render('backend|sale/order/panes/components/rule', $data);
            $item['data']['components'][$price_rule_id]['rendered'] = $html;
        }
    }

    /**
     * Adds a key containing translations for the entity
     * @param array $item
     * @param string $entity
     * @param \gplcart\core\models\TranslationEntity $model
     */
    public function setItemTranslation(array &$item, $entity, $model)
    {
        if (isset($item["{$entity}_id"]) && $model->isSupportedEntity($entity)) {
            foreach ($model->getList(array('entity' => $entity, 'entity_id' => $item["{$entity}_id"])) as $translation) {
                $item['translation'][$translation['language']] = $translation;
            }
        }
    }

    /**
     * Adds images to an entity
     * @param array $item
     * @param string $entity
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemImages(&$item, $entity, $image_model)
    {
        if (!empty($item[$entity . '_id'])) {

            $options = array(
                'entity' => $entity,
                'entity_id' => $item[$entity . '_id']
            );

            $item['images'] = (array) $image_model->getList($options);
        }
    }

}
