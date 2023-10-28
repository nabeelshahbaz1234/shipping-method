<?php
declare(strict_types=1);

namespace RltSquare\UpdateShippingPrice\Helper\Csv;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use RltSquare\UpdateShippingPrice\Logger\Logger;

class GetCsvPrice
{
    private Session $checkoutSession;
    private Csv $csvProcessor;
    private DirectoryList $directoryList;
    private Logger $logger;

    /**
     * @param Session $checkoutSession
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param Logger $logger
     */
    public function __construct(
        Session       $checkoutSession,
        Csv           $csvProcessor,
        DirectoryList $directoryList,
        Logger       $logger

    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
    }

    /**
     * @throws FileSystemException
     * @throws Exception
     */
    public function CsvPrice(): void
    {
        $pubMediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $ds = DIRECTORY_SEPARATOR;
        $dirTest = '/csv';
        $newFileName = 'parcel.csv'; // Get Hardcoded file name
        $file = $pubMediaDir . $dirTest . $ds . $newFileName;
        $csvFilePath = $file;
        $data = $this->csvProcessor->getData($csvFilePath);
        if ($data) {
            $priceToFind = $this->checkoutSession->getCustomShippingCost();

            foreach ($data as $row) {
                $from = floatval($row[0]);
                $to = floatval($row[1]);
                $price = floatval($row[2]);

                if ($priceToFind >= $from && $priceToFind <= $to) {
                    // Price found, you can use it here or store it as needed
                    $this->checkoutSession->setCustomShippingCost($price);
                    break; // If you only want to find the price once and exit the loop
                }
            }
        }else{
            $this->logger->error("No Price found in Csv");
        }
    }


}
