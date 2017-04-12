<?php

namespace Voilaah\PointsRedemption\Api\Data;

interface PointsTransactionInterface
{

    const TRANSACTION_ID        = 'id';
    const ORDER_ID              = 'order_id';
    const CUSTOMER_ID           = 'customer_id';
    const DESCRIPTION           = 'description';
    const TYPE                  = 'type';
    const AMOUNT                = 'amount';
    const CREATED_AT            = 'created_at';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return integer
     */
    public function getOrderId();

    /**
     * @return integer
     */
    public function getCustomerId();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return integer
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param integer $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param integer $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @param integer $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @param double $value
     * @return $this
     */
    public function setAmount($value);

    /**
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt($created_at);

}