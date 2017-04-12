<?php

namespace Voilaah\PointsRedemption\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Voilaah\PointsRedemption\Api\Data\PointsTransactionInterface;

class PointsTransaction extends AbstractModel implements PointsTransactionInterface, IdentityInterface
{

    const CACHE_TAG = 'pointsredemption_transactions';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = 'pointsredemption_transactions';

    protected function _construct()
    {
        $this->_init('Voilaah\PointsRedemption\Model\ResourceModel\PointsTransaction');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id)
    {
        return $this->setData(self::TRANSACTION_ID, $id);
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @param int $customerId
     * @return mixed
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @param string $description
     * @return mixed
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @param float $value
     * @return mixed
     */
    public function setAmount($value)
    {
        return $this->setData(self::AMOUNT, $value);
    }

    /**
     * @param string $created_at
     * @return mixed
     */
    public function setCreatedAt($created_at)
    {
        return $this->setData(self::CREATED_AT, $created_at);
    }

}