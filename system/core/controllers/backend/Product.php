<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Category as CategoryModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\ProductClass as ProductClassModel,
    gplcart\core\models\CategoryGroup as CategoryGroupModel,
    gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\core\traits\Category as CategoryTrait;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends BackendController
{

    use CategoryTrait;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Entity translation model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * URL model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current updating product
     * @var array
     */
    protected $data_product = array();

    /**
     * @param ProductModel $product
     * @param ProductClassModel $product_class
     * @param CategoryModel $category
     * @param CategoryGroupModel $category_group
     * @param PriceModel $price
     * @param CurrencyModel $currency
     * @param AliasModel $alias
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(ProductModel $product, ProductClassModel $product_class,
            CategoryModel $category, CategoryGroupModel $category_group, PriceModel $price,
            CurrencyModel $currency, AliasModel $alias, TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->alias = $alias;
        $this->price = $price;
        $this->product = $product;
        $this->category = $category;
        $this->currency = $currency;
        $this->product_class = $product_class;
        $this->category_group = $category_group;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Displays the product overview page
     */
    public function listProduct()
    {
        $this->actionListProduct();

        $this->setTitleListProduct();
        $this->setBreadcrumbListProduct();
        $this->setFilterListProduct();
        $this->setPagerListProduct();

        $this->setData('products', $this->getListProduct());
        $this->setData('currencies', $this->currency->getList(array('enabled' => true)));

        $this->outputListProduct();
    }

    /**
     * Set filter on the product overview page
     */
    protected function setFilterListProduct()
    {
        $allowed = array('title', 'sku_like', 'price', 'stock', 'status',
            'store_id', 'product_id', 'currency');

        $this->setFilter($allowed);
    }

    /**
     * Set pager
     * @return array
     */
    protected function setPagerListProduct()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->product->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Applies an action to the selected products
     */
    protected function actionListProduct()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('product_edit')) {
                $updated += (int) $this->product->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('product_delete')) {
                $deleted += (int) $this->product->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Returns an array of products
     * @return array
     */
    protected function getListProduct()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        $products = (array) $this->product->getList($conditions);
        return $this->prepareListProduct($products);
    }

    /**
     * Prepare an array of products
     * @param array $products
     * @return array
     */
    protected function prepareListProduct(array $products)
    {
        foreach ($products as &$product) {
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemUrlEntity($product, $this->store, 'product');
        }

        return $products;
    }

    /**
     * Sets title on the product overview page
     */
    protected function setTitleListProduct()
    {
        $this->setTitle($this->text('Products'));
    }

    /**
     * Sets breadcrumbs on the product overview page
     */
    protected function setBreadcrumbListProduct()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the product overview page
     */
    protected function outputListProduct()
    {
        $this->output('content/product/list');
    }

    /**
     * Displays the product edit form
     * @param integer|null $product_id
     */
    public function editProduct($product_id = null)
    {
        $this->setProduct($product_id);
        $this->setTitleEditProduct();
        $this->setBreadcrumbEditProduct();

        $this->setData('product', $this->data_product);
        $this->setData('size_units', $this->product->getSizeUnits());
        $this->setData('weight_units', $this->product->getWeightUnits());
        $this->setData('default_currency', $this->currency->getDefault());
        $this->setData('languages', $this->language->getList(array('in_database' => true)));
        $this->setData('subtract_default', $this->config->get('product_subtract', 0));
        $this->setData('classes', $this->product_class->getList(array('status' => 1)));

        $this->submitEditProduct();

        $this->setDataFieldsEditProduct();
        $this->setDataAuthorEditProduct();
        $this->setDataRelatedEditProduct();
        $this->setDataCategoriesEditProduct();
        $this->setDataImagesEditProduct();

        $this->setJsEditProduct();
        $this->outputEditProduct();
    }

    /**
     * Returns an array of categories keyed by a type
     * @param integer $store_id
     * @return array
     */
    protected function getListCategoryProduct($store_id)
    {
        $types = $this->category_group->getTypes();

        $categories = array();
        foreach (array_keys($types) as $type) {

            $op = array(
                'type' => $type,
                'store_id' => $store_id
            );

            $data = $this->getCategoryOptionsByStore($this->category, $this->category_group, $op);
            $categories[$type] = reset($data);
        }

        return $categories;
    }

    /**
     * Set a product data
     * @param integer $product_id
     */
    protected function setProduct($product_id)
    {
        if (is_numeric($product_id)) {
            $product = $this->product->get($product_id);
            if (empty($product)) {
                $this->outputHttpStatus(404);
            }

            $this->data_product = $this->prepareProduct($product);
        }
    }

    /**
     * Prepare an array of product data
     * @param array $product
     * @return array
     */
    protected function prepareProduct(array $product)
    {
        $options = array(
            'store_id' => $product['store_id'],
            'product_id' => $product['product_id']
        );

        $product['related'] = $this->product->getRelated($options);
        $product['alias'] = $this->alias->getByEntity('product', $product['product_id']);
        $product['price'] = $this->price->decimal($product['price'], $product['currency']);

        $this->setItemProductCombination($product, $this->image, $this->price);
        $this->setItemTranslation($product, 'product', $this->translation_entity);
        return $product;
    }

    /**
     * Handles a submitted product
     */
    protected function submitEditProduct()
    {
        if ($this->isPosted('delete')) {
            $this->deleteProduct();
        } else if ($this->isPosted('save') && $this->validateEditProduct()) {
            $this->deleteImagesProduct();
            if (isset($this->data_product['product_id'])) {
                $this->updateProduct();
            } else {
                $this->addProduct();
            }
        }
    }

    /**
     * Delete product images
     * @return boolean
     */
    protected function deleteImagesProduct()
    {
        $file_ids = $this->getPosted('delete_images', array(), true, 'array');
        return $this->image->delete($file_ids);
    }

    /**
     * Deletes a product
     */
    protected function deleteProduct()
    {
        $this->controlAccess('product_delete');

        if ($this->product->delete($this->data_product['product_id'])) {
            $this->redirect('admin/content/product', $this->text('Product has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Product has not been deleted'), 'warning');
    }

    /**
     * Validates an array of submitted product data
     * @return bool
     */
    protected function validateEditProduct()
    {
        $this->setSubmitted('product', null, false);
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('subtract');
        $this->setSubmitted('form', true);
        $this->setSubmitted('update', $this->data_product);

        if (isset($this->data_product['product_id'])) {
            $this->setSubmitted('user_id', $this->data_product['user_id']);
            $this->setSubmitted('created', $this->data_product['created']);
            $this->setSubmitted('modified', $this->data_product['modified']);
            $this->setSubmitted('currency', $this->data_product['currency']);
            $this->setSubmitted('product_id', $this->data_product['product_id']);
        } else {
            $this->setSubmitted('user_id', $this->uid);
            $this->setSubmitted('currency', $this->currency->getDefault());
        }

        $this->validateComponent('product');

        return !$this->hasErrors();
    }

    /**
     * Updates a product
     */
    protected function updateProduct()
    {
        $this->controlAccess('product_edit');

        if ($this->product->update($this->data_product['product_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/product', $this->text('Product has been updated'), 'success');
        }

        $this->redirect('', $this->text('Product has not been updated'), 'warning');
    }

    /**
     * Adds a new product
     */
    protected function addProduct()
    {
        $this->controlAccess('product_add');

        if ($this->product->add($this->getSubmitted())) {
            $this->redirect('admin/content/product', $this->text('Product has been added'), 'success');
        }

        $this->redirect('', $this->text('Product has not been added'), 'warning');
    }

    /**
     * Sets the product author data
     */
    protected function setDataAuthorEditProduct()
    {
        $user_id = $this->getData('product.user_id');

        if (!empty($user_id)) {
            $user = $this->user->get($user_id);
            $this->setData('product.author', $user['email']);
        }
    }

    /**
     * Sets the product categories data
     */
    protected function setDataCategoriesEditProduct()
    {
        $categories = $this->getListCategoryProduct($this->getData('store_id'));
        $this->setData('categories', $categories);
    }

    /**
     * Sets attributes/options product data
     */
    protected function setDataFieldsEditProduct()
    {
        $output_field_form = false;
        $get_product_class_id = $this->getQuery('product_class_id');
        $product_class_id = $this->getData('product.product_class_id', 0);

        if (isset($get_product_class_id)) {
            $output_field_form = true;
            $product_class_id = (int) $get_product_class_id;
        }

        $data = array(
            'product' => $this->getData('product'),
            'fields' => $this->product_class->getFieldData($product_class_id)
        );

        $options = $this->render('content/product/options', $data);
        $attributes = $this->render('content/product/attributes', $data);

        $this->setData('option_form', $options);
        $this->setData('attribute_form', $attributes);

        if ($output_field_form) {
            $this->response->outputHtml($attributes . $options);
        }
    }

    /**
     * Sets related products
     */
    protected function setDataRelatedEditProduct()
    {
        $product_ids = $this->getData('product.related');

        $products = array();
        if (!empty($product_ids)) {
            $products = (array) $this->product->getList(array('product_id' => $product_ids));
        }

        $options = array(
            'entity' => 'product',
            'entity_id' => $product_ids,
            'template_item' => 'backend|content/product/suggestion'
        );

        foreach ($products as &$product) {
            $this->setItemThumb($product, $this->image, $options);
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemRendered($product, array('item' => $product), $options);
        }

        $widget = array(
            'multiple' => true,
            'name' => 'product[related]',
            'products' => $products
        );

        $this->setData('product_picker', $this->getWidgetProductPicker($widget));
    }

    /**
     * Sets product attached data
     */
    protected function setDataImagesEditProduct()
    {
        $options = array(
            'entity' => 'product',
            'images' => $this->getData('product.images', array())
        );

        $this->setItemThumb($options, $this->image);
        $this->setData('image_widget', $this->getWidgetImages($this->language, $options));
    }

    /**
     * Sets Java scripts on the edit product page
     */
    protected function setJsEditProduct()
    {
        $this->setJsSettings('product', $this->data_product);
    }

    /**
     * Sets titles on the product edit form
     */
    protected function setTitleEditProduct()
    {
        if (isset($this->data_product['product_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_product['title']));
        } else {
            $title = $this->text('Add product');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the product edit page
     */
    protected function setBreadcrumbEditProduct()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Products'),
            'url' => $this->url('admin/content/product')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the product edit page
     */
    protected function outputEditProduct()
    {
        $this->output('content/product/edit');
    }

}
