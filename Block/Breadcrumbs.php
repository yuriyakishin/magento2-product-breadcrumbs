<?php
namespace Yu\ProductBreadcrumbs\Block;

use Magento\Framework\View\Element\Template\Context;

class Breadcrumbs extends \Magento\Theme\Block\Html\Breadcrumbs
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $productRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Breadcrumbs constructor.
     * @param Context $context
     * @param \Magento\Catalog\Block\Product\Context $productContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Block\Product\Context $productContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = []
    )
    {
        $this->productRegistry = $productContext->getRegistry();
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createCrumbs()
    {
        $this->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]
        );

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRegistry->registry('product');

        $categoryCollection = $this->categoryCollectionFactory->create();

        $categoryCollection
            ->joinField(
                'product_id',
                'catalog_category_product',
                'product_id',
                'category_id = entity_id',
                null
            )
            ->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)
            ->addAttributeToFilter(
                'path',
                ['like' => "1/" . $this->storeManager->getStore()->getRootCategoryId() . "/%"]
            )
            ->addFieldToFilter(
                'product_id',
                (int)$product->getEntityId()
            );

        $categoryCollection->setPageSize(1);
        $categories = $categoryCollection->getFirstItem()->getParentCategories();
        foreach ($categories as $category) {
            $this->addCrumb('category' . $category->getId(), [
                    'label' => $category->getName(),
                    'title' => $category->getName(),
                    'link' => $category->getUrl()
                ]
            );
        }

        $this->addCrumb('product',
            [
                'label' => $product->getName(),
                'title' => $product->getName(),
                'link' => '',
                'last' => true
            ]
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        $this->createCrumbs();

        return parent::_toHtml();
    }
}
