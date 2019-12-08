<?php

namespace JonathanMartz\Statistics\Cron;

use \Magento\Store\Model\Store;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class SendData
 * @package JonathanMartz\Statistics\Cron
 */
class SendData
{
    /**
     * Module enabled
     */
    const XML_PATH_ENABLED = 'magento/statistics/enable';

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * SendData constructor.
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }


    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->isEnabled()) {

        }
    }
}

