<?php

namespace RltSquare\UpdateShippingPrice\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;

class Csv  extends File
{

    protected function _getAllowedExtensions(): array
    {
        return ['csv'];
    }
}
