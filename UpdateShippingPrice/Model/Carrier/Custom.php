<?php

declare(strict_types=1);

namespace RltSquare\UpdateShippingPrice\Model\Carrier;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * @class Custom
 */
class Custom extends AbstractCarrier implements CarrierInterface
{

    protected $_code = 'custom';
    protected ResultFactory $rateResultFactory;
    protected MethodFactory $rateMethodFactory;

    protected CheckoutSession $checkoutSession;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory         $rateErrorFactory,
        LoggerInterface      $logger,
        ResultFactory        $rateResultFactory,
        MethodFactory        $rateMethodFactory,
        CheckoutSession      $checkoutSession,
        array                $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return ['custom' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result|null
     */
    public function collectRates(RateRequest $request): Result|bool|null
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->rateResultFactory->create();

        $method = $this->rateMethodFactory->create();

        $method->setCarrier('custom');
        // Get shipping name and cost from the observer
        $shippingName = $this->checkoutSession->getCustomShippingName();
        // Get shipping cost from the session
        $shippingCost = $this->checkoutSession->getCustomShippingCost();

        $method->setCarrierTitle($shippingName);
        $method->setMethod('custom');
        $method->setMethodTitle($shippingName);

        // Use the retrieved shipping cost
        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        return $result;
    }
}

