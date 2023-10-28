<?php
declare(strict_types=1);

namespace RltSquare\UpdateShippingPrice\Observer;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use RltSquare\UpdateShippingPrice\Action\BigBuyPushDetailsToWebservice;
use RltSquare\UpdateShippingPrice\Helper\Csv\GetCsvPrice;
use RltSquare\UpdateShippingPrice\Logger\Logger;

/**
 * @class CartUpdate
 */
class CartUpdate implements ObserverInterface
{

    private Session $checkoutSession;
    private BigBuyPushDetailsToWebservice $pushDetailsToWebservice;
    private GetCsvPrice $getCsvPrice;
    private Logger $logger;

    /**
     * @param CheckoutSession $checkoutSession
     * @param BigBuyPushDetailsToWebservice $pushDetailsToWebservice
     * @param GetCsvPrice $getCsvPrice
     * @param Logger $logger
     */
    public function __construct(
        CheckoutSession               $checkoutSession,
        BigBuyPushDetailsToWebservice $pushDetailsToWebservice,
        GetCsvPrice                   $getCsvPrice,
        Logger                        $logger
    )

    {
        $this->checkoutSession = $checkoutSession;
        $this->pushDetailsToWebservice = $pushDetailsToWebservice;
        $this->getCsvPrice = $getCsvPrice;
        $this->logger = $logger;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws GuzzleException
     * @throws Exception
     */
    public function execute(Observer $observer): void
    {

        try {
            $products = $this->checkoutSession->getQuote()->getAllVisibleItems();
            if ($products) {

                $productData = [];

                foreach ($products as $product) {
                    $productSku = $product->getSku();
                    $productQuantity = $product->getQty();

                    // Create an array for each product
                    $productItem = [
                        'reference' => $productSku,
                        'quantity' => $productQuantity,
                    ];

                    // Add the product item to the product data array
                    $productData[] = $productItem;
                }

                // Construct the payload with the product data
                $payload = [
                    'order' => [
                        'products' => $productData, // Include the array of product items
                        'delivery' => [
                            'id' => 1234,
                            'isoCountry' => 'DK',
                            'postCode' => '9100',
                        ],
                    ],
                ];

                // Get Data From Big Buy API endpoint
                $results = $this->pushDetailsToWebservice->execute($payload);
                $shippingName = $results['lowest_cost_name'];
                $shippingCost = $results['lowest_cost'];

                $this->checkoutSession->setCustomShippingName($shippingName);
                $this->checkoutSession->setCustomShippingCost($shippingCost);

                // call the helper function to get the csv price
                $this->getCsvPrice->CsvPrice();
            }else{
                $this->logger->error("No Items found in the cart");
            }
        } catch (Exception $e) {
            // Log the error message
            $this->logger->error('Error occur in the process: ' . $e->getMessage());

        }
    }
}
