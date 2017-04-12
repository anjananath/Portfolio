<?php

namespace Voilaah\PointsRedemption\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PointsTransaction extends AbstractDb
{

    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('voilaah_pointsredemption_transactions', 'id');
    }

    protected function _beforeSave(AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }

}