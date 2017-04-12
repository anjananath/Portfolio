<?php

namespace Voilaah\PointsRedemption\Model\ResourceModel\PointsTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class PointsTransactionCollection extends AbstractCollection {

    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Voilaah\PointsRedemption\Model\PointsTransaction', 'Voilaah\PointsRedemption\Model\ResourceModel\PointsTransaction');
    }

}