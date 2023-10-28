<?php
declare(strict_types=1);


namespace RltSquare\UpdateShippingPrice\Helper\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @Config
 */
class Config
{
    /**
     * @const CONFIG_PATH_ENABLED
     */
    const CONFIG_PATH_ENABLED = 'sales/shipping_cost/enabled';
    const CONFIG_PATH_API_TOKEN = 'sales/shipping_cost/api_token';
    const CONFIG_PATH_API_URL = 'sales/shipping_cost/api_url';


    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isEnabled(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getApiToken(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_API_TOKEN, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getApiUrl(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_API_URL, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }
}
