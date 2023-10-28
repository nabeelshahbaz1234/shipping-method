<?php
declare(strict_types=1);

namespace RltSquare\UpdateShippingPrice\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * @index
 */
class Index extends Action
{

    public const XML_PATH_NAME_FILE = 'sales/shipping_cost/csv_file_upload';

    protected ScopeConfigInterface $scopeConfig;

    protected PageFactory $resultPageFactory;
    protected ResultFactory $resultRedirect;
    protected Csv $csvProcessor;

    protected DirectoryList $directoryList;
    private File $driverFile;

    /**
     * @param Context $context
     * @param Csv $csvProcessor
     * @param PageFactory $resultPageFactory
     * @param DirectoryList $directoryList
     * @param ResultFactory $resultRedirect
     * @param ScopeConfigInterface $scopeConfig
     * @param File $driverFile
     */
    public function __construct(
        Context              $context,
        Csv                  $csvProcessor,
        PageFactory          $resultPageFactory,
        DirectoryList        $directoryList,
        ResultFactory        $resultRedirect,
        ScopeConfigInterface $scopeConfig,
        File                 $driverFile,

    )
    {
        $this->resultRedirect = $resultRedirect;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->resultPageFactory = $resultPageFactory;
        $this->driverFile = $driverFile;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setUrl($url);

        if (!$this->readCsv()) {
            $this->messageManager->addErrorMessage(
                "Csv sheet is invalid. "
            );
            return $resultRedirect;
        }
        $this->messageManager->addSuccessMessage("CSV imported successfully");
        return $resultRedirect;
    }

    /**
     * @throws FileSystemException
     * @throws Exception
     */
    public function readCsv(): bool
    {
        $pubMediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $fieName = $this->getConfigFileName();
        $ds = DIRECTORY_SEPARATOR;
        $dirTest = '/csv';
        $newFileName = 'parcel.csv'; // Hardcoded new file name
        $file = $pubMediaDir . $dirTest . $ds . $fieName;
        $csvData = $this->csvProcessor->getData($file);
        if (!file_exists($file) || !$this->validateCsv($csvData) || !$this->validateFile($file)) {
            return false;
        }
        if (!empty($file)) {
            // Rename the file before saving it
            $newFilePath = $pubMediaDir . $dirTest . $ds . $newFileName;
            if (rename($file, $newFilePath)) {
                $path = $this->directoryList->getPath(DirectoryList::MEDIA) . $dirTest;
                $files = $this->driverFile->readDirectory($path);
                foreach ($files as $csv) {
                    if ($csv != $newFilePath) {
                        $this->driverFile->deleteFile($csv);
                    }
                }
            } else {
                return false;
            }
        }
        return true;
    }


    /**
     * @param string $nameFile
     * @return mixed
     */
    public function getConfigFileName(string $nameFile = self::XML_PATH_NAME_FILE): mixed
    {
        return $this->scopeConfig->getValue($nameFile, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param array $csvData
     * @return bool
     */
    private function validateCsv(array $csvData): bool
    {
        // Define the expected header
        $expectedHeader = ['bigbuy_shipping_charges_from', 'bigbuy_shipping_charges_to', 'Lawaro_shipping_charge (DKK)'];

        // Check if the first row matches the expected header
        if ($csvData[0] !== $expectedHeader) {
            return false;
        }

        // Iterate through the remaining rows and validate them
        for ($i = 1; $i < count($csvData); $i++) {
            $row = $csvData[$i];

            // Check if the row has exactly 3 columns and the first column is not empty
            if (count($row) !== 3 || empty($row[0])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws LocalizedException
     */
    public function validateFile($filePath): bool
    {
        if (!is_readable($filePath)) {
            throw new LocalizedException(
                __('The uploaded file is not readable.')
            );
        }

        // Check if the file path points to a directory
        if (is_dir($filePath)) {
            throw new LocalizedException(
                __('Invalid file path. A directory was provided instead of a CSV file.')
            );
        }

        $fileInfo = pathinfo($filePath);
        $fileExtension = strtolower($fileInfo['extension']);

        // Check if the file extension exists before accessing it
        if (!isset($fileInfo['extension'])) {
            throw new LocalizedException(
                __('File extension not found in path. Only CSV files are allowed.')
            );
        }

        // Check if the file extension is 'csv'
        if ($fileExtension !== 'csv') {
            throw new LocalizedException(
                __('Invalid file type. Only CSV files are allowed.')
            );
        }

        return true;
    }

}
