<?php
declare(strict_types=1);

namespace RltSquare\UpdateShippingPrice\Action;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;
use function json_decode;

/**
 * @class BigBuyProcessResponse
 */
class BigBuyProcessResponse
{

    /**
     * @throws LocalizedException
     */
    public function processResponse(ResponseInterface $response): ?array
    {
        $responseBody = (string)$response->getBody();
        try {
            $responseData = json_decode($responseBody, true);
        } catch (Exception $e) {
            $responseData = [];
        }

        $allowedCarriers = ['GLS', 'DPD', 'TNT', 'DHL', 'UPS', 'DB Schenker', 'Dachser','Standard Shipment'];
        $lowestCost = null;
        $lowestCostName = null;

        if (isset($responseData['shippingOptions']) && is_array($responseData['shippingOptions'])) {
            foreach ($responseData['shippingOptions'] as $shippingOption) {
                if (
                    isset($shippingOption['cost']) &&
                    isset($shippingOption['shippingService']['name']) &&
                    in_array($shippingOption['shippingService']['name'], $allowedCarriers) &&
                    $shippingOption['shippingService']['transportMethod'] !== 'plane'
                ) {
                    $cost = $shippingOption['cost'];
                    $shippingName = $shippingOption['shippingService']['name'];

                    if ($lowestCost === null || $cost < $lowestCost) {
                        // Update the lowest cost and associated shipping name
                        $lowestCost = $cost;
                        $lowestCostName = $shippingName;
                    }
                }
            }
        }

        if ($lowestCost !== null && $lowestCostName !== null) {
            return [
                'lowest_cost' => $lowestCost,
                'lowest_cost_name' => $lowestCostName,
            ];
        } else {
            // Handle the case where no valid shipping options are found
            $errorMsg = __('No shipping options found in the response.');
            throw new LocalizedException($errorMsg);
        }
    }

}
