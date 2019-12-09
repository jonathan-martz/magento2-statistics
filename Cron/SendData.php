<?php

namespace JonathanMartz\Statistics\Cron;

use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Store\Model\Store;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use \Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use \Magento\Customer\Model\Customer;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Class SendData
 * @package JonathanMartz\Statistics\Cron
 */
class SendData
{
    /**
     * @var array
     */
    public $types = [
        'simple', 'grouped', 'bundle', 'downloadable', 'configurable'
    ];

    /**
     * Module enabled
     */
    const XML_PATH_ENABLED = 'magento/statistics/enable';

    /**
     * Endpoint Url
     */
    const XML_PATH_ENDPOINT = 'magento/statistics/endpoint';

    /**
     * enabled Product Data
     */
    const XML_PATH_ENABLED_PRODUCT = 'magento/statistics/enable_product';

    /**
     * enabled Product Data
     */
    const XML_PATH_ENABLED_MODULES = 'magento/statistics/enable_modules';

    /**
     * enabled Category Data
     */
    const XML_PATH_ENABLED_CATEGORY = 'magento/statistics/enable_category';

    /**
     * enabled Order Data
     */
    const XML_PATH_ENABLED_ORDER = 'magento/statistics/enable_order';

    /**
     * enabled Store Data
     */
    const XML_PATH_ENABLED_STORE = 'magento/statistics/enable_store';

    /**
     * enabled Customer Data
     */
    const XML_PATH_ENABLED_CUSTOMER = 'magento/statistics/enable_customer';

    /**
     * enabled Development Agency
     */
    const XML_PATH_ENABLED_DEVELOPMENT = 'magento/statistics/enable_development';

    /**
     * enabled Design Agency
     */
    const XML_PATH_ENABLED_DESIGN = 'magento/statistics/enable_design';

    /**
     * enabled Development Agency
     */
    const XML_PATH_DEVELOPMENT_NAME = 'magento/statistics/development_name';

    /**
     * enabled Development Agency
     */
    const XML_PATH_DEVELOPMENT_WEBSITE = 'magento/statistics/development_website';

    /**
     * enabled Design Agency
     */
    const XML_PATH_DESIGN_NAME = 'magento/statistics/design_name';

    /**
     * enabled Design Agency
     */
    const XML_PATH_DESIGN_WEBSITE = 'magento/statistics/design_website';

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollection
     */
    public $productCollection;

    /**
     * @var CategoryCollection
     */
    public $categoryCollection;

    /**
     * @var OrderCollection
     */
    public $orderCollection;

    /**
     * @var Customer
     */
    public $customer;

    /**
     * SendData constructor.
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $productMetadata
     * @param StoreManagerInterface $storeManager
     * @param ProductCollection $productCollection
     * @param Customer $customers
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager,
        ProductCollection $productCollection,
        CategoryCollection $categoryCollection,
        Customer $customers,
        OrderCollection $orderCollection,
        ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollection;
        $this->categoryCollection = $categoryCollection;
        $this->customer = $customers;
        $this->orderCollection = $orderCollection;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENDPOINT);
    }

    /**
     * @return bool
     */
    public function allowProductData()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_PRODUCT);
    }

    /**
     * @return bool
     */
    public function allowModulesData()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_MODULES);
    }

    /**
     * @return bool
     */
    public function allowCategoryData()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_CATEGORY);
    }

    /**
     * @return bool
     */
    public function allowOrderData()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_ORDER);
    }

    /**
     * @return bool
     */
    public function allowCustomerData()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_CUSTOMER);
    }

    /**
     * @return bool
     */
    public function allowStoreData()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_STORE);
    }

    /**
     * @return bool
     */
    public function isEnabledDevelopmentAgency(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_DEVELOPMENT);
    }

    /**
     * @return bool
     */
    public function isEnabledDesignAgency(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED_DESIGN);
    }

    /**
     * @return string|null
     */
    public function getDevelopmentAgencyName(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DEVELOPMENT_NAME);
    }

    /**
     * @return string|null
     */
    public function getDevelopmentAgencyWebsite(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DEVELOPMENT_WEBSITE);
    }

    /**
     * @return string|null
     */
    public function getDesignAgencyName(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DESIGN_NAME);
    }

    /**
     * @return string|null
     */
    public function getDesignAgencyWebsite(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DESIGN_WEBSITE);
    }


    /**
     * @return array
     */
    public function getBaseUrls(): array
    {
        $data = [];

        $stores = $this->storeManager->getStores();
        foreach ($stores as $key => $store) {
            $id = $store->getId();
            $url = $store->getBaseUrl();
            $data[$id] = $url;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getFullMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @return float
     */
    public function getSales(): array
    {
        $start = date('Y-m-d H:i:s', strtotime("now"));
        $end = date('Y-m-d H:i:s', strtotime("-7 day"));

        $orderCollection = $this->orderCollection->create()->addFieldToSelect(array('*'));
        $orderCollection->addFieldToFilter('created_at', ['lteq' => $start])->addFieldToFilter('created_at', ['gteq' => $end]);

        $turnover = 0;
        foreach($orderCollection as $key => $order){
            $turnover += $order->getGrandTotal();
        }

        return [
            'count' => $orderCollection->count(),
            'turnover' => $turnover
        ];
    }

    /**
     * @return string
     */
    public function getPhpVersion(): string
    {
        preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
        return $match[0];
    }

    /**
     * @return array
     */
    public function getVendorModules(): array
    {
        $vendors = scandir('vendor');
        $data = [];

        foreach ($vendors as $key => $vendor) {
            if (is_dir('vendor/' . $vendor)) {
                if ($vendor != '.' && $vendor != '..' && $vendor != 'magento' && $vendor != 'composer') {
                    $data[$vendor] = [];
                    $modules = scandir('vendor/' . $vendor);
                    foreach ($modules as $key => $module) {
                        if (is_dir('vendor/' . $vendor)) {
                            if ($module != '.' && $module != '..') {
                                $data[$vendor][$module] = true;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getProductCount(): int
    {
        $collection = $this->productCollection;
        return $collection->count();
    }

    /**
     * @return array
     */
    public function getProductTypeCount(): array
    {
        $data = [];

        foreach ($this->types as $key => $type) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToFilter('type_id', ['eq' => $type]);
            $data[$type] = $collection->count();
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getCustomerCount(): int
    {
        return $this->customer->getCollection()->count();
    }

    /**
     * @return int
     */
    public function getStoreCount(): int
    {
        $stores = $this->storeManager->getStores();
        return count($stores);
    }

    /**
     * @return string|null
     */
    public function getDevelopmentAgency(): array
    {
        return [
            'name' => $this->getDevelopmentAgencyName(),
            'website' => $this->getDevelopmentAgencyWebsite()
        ];
    }

    /**
     * @return array
     */
    public function getDesignAgency(): array
    {
        return [
            'name' => $this->getDesignAgencyName(),
            'website' => $this->getDesignAgencyWebsite()
        ];
    }

    /**
     * @return array
     */
    public function getProductOtherTypes(): array
    {
        $types = [];

        $collection = $this->productCollection;
        $collection->addFieldToFilter('type_id', 'simple');

        foreach ($collection->load() as $key => $product) {
            if (!in_array($product->getData('type_id'), $this->types)) {
                $types[$product->getData('type_id')] = true;
            }
        }

        return $types;
    }

    /**
     * @return int
     */
    public function getCategoryCount(): int
    {
        $collection = $this->categoryCollection;
        return $collection->count();
    }

    /**
     * @return string
     */
    public function generatePostData(): string
    {
        $data = [];

        // default Data
        $data['magento'] = [
            'urls' => $this->getBaseUrls(),
            'version' => $this->getFullMagentoVersion(),
            'php' => $this->getPhpVersion()
        ];

        if ($this->allowModulesData()) {
            $data['modules'] = $this->getVendorModules();
        }

        if ($this->allowProductData()) {
            $data['products'] = [
                'all' => $this->getProductCount(),
                'default' => $this->getProductTypeCount(),
                'other' => $this->getProductOtherTypes()
            ];
        }

        if ($this->allowCategoryData()) {
            $data['category'] = [
                'count' => $this->getCategoryCount()
            ];
        }

        if ($this->allowOrderData()) {
            $data['sales'] = $this->getSales();
        }

        if ($this->allowCustomerData()) {
            $data['customer'] = [
                'count' => $this->getCustomerCount()
            ];
        }

        if ($this->allowStoreData()) {
            $data['store'] = [
                'count' => $this->getStoreCount()
            ];
        }

        if ($this->isEnabledDevelopmentAgency()) {
            $data['development'] = $this->getDevelopmentAgency();
        }

        if ($this->isEnabledDesignAgency()) {
            $data['design'] = $this->getDesignAgency();
        }

        return json_encode($data, JSON_FORCE_OBJECT);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->isEnabled()) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->getApiUrl());
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->generatePostData());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }
}

