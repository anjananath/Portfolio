<?php

namespace Voilaah\PointsRedemption\Block\Points;

use Voilaah\PointsRedemption\Model\ResourceModel\PointsTransaction;
use Magento\Sales\Model\Order;

class History extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'points/history.phtml';

    protected $_customerSession;

    protected $transactionCollectionFactory;

    private $transactions;

    private $orderModel;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        PointsTransaction\PointsTransactionCollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        Order $order,
        array $data = []
    ) {
        $this->transactionCollectionFactory = $collectionFactory;
        $this->_customerSession = $customerSession;
        $this->transactions = false;
        $this->orderModel = $order;
        parent::__construct($context, $data);
    }

    public function getPointsTransactions()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        if (!$this->transactions) {
            $this->transactions = $this->transactionCollectionFactory->create();
            $this->transactions->addFieldToFilter('customer_id', [
                'where' => $customerId
            ]);
        }

        return $this->transactions;
    }

    public function getOrder($id)
    {
        $data = $this->orderModel->load($id);

        return $data;
    }
}
