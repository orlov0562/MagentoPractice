<?php

class Orlov_HomeProducts_Block_List extends Mage_Core_Block_Template
{
    private $config = array();

    public function _construct()
    {
        parent::_construct();
        $this->loadDefaultConfig();
    }

    private function loadDefaultConfig()
    {
        $this->config = array(
          'PRODUCTS_PER_PAGE'       => 12,
          'FILTER_IN_STOCK_ONLY'    => TRUE,
          'FILTER_PRICE_PREDICATE'  => 'gteq',
          'FILTER_PRICE_VALUE'      => 40,
          'ORDER_FIELD'             => 'price',
          'ORDER_DIRECTION'         => 'ASC',
        );
    }

    public function getProductsList(array $config=array())
    {
        $config = array_merge($this->config, $config);
        $productCollection = $this->getConfiguredProductCollection($config);
        return $productCollection->load();
    }

    private function getConfiguredProductCollection(array $config)
    {
        // Prepare products collection
        $productCollection = Mage::getModel('catalog/product')->getCollection();

        // Add EAV fields

//        $productCollection->addAttributeToSelect('*');
        $productCollection->addAttributeToSelect('price');
        $productCollection->addAttributeToSelect('name');
        $productCollection->addAttributeToSelect('url_path');
        $productCollection->addAttributeToSelect('short_description');
        $productCollection->addAttributeToSelect('small_image');

        // Add filters
        if ($config['USE_IN_STOCK_FILTER'])
            $productCollection->addIdFilter($this->getInStockProductIds());

        $productCollection->addFieldToFilter('price', array(
            $config['FILTER_PRICE_PREDICATE'] => $config['FILTER_PRICE_VALUE']
        ));

        // Add sorting
        $productCollection->setOrder($config['ORDER_FIELD'], $config['ORDER_DIRECTION']);

        // Add limits
        $productCollection->setPageSize($config['PRODUCTS_PER_PAGE']);

        return $productCollection;
    }

    private function getInStockProductIds()
    {
        $stockCollection = Mage::getModel('cataloginventory/stock_item')
        ->getCollection()
        ->addFieldToFilter('is_in_stock', true);

        $productIds = array();

        foreach ($stockCollection as $item) {
            $productIds[] = $item->getOrigData('product_id');
        }

        return $productIds;
    }
}