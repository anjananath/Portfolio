<?php
 
namespace Voilaah\PointsRedemption\Model;
 
/**
 * Pay In Store payment method model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
 
    const PAYMENT_METHOD_PAYBOX_CODE = 'pointsredeem';
    protected $_code = self::PAYMENT_METHOD_PAYBOX_CODE;

    protected $_isOffline = true;

    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    public function getMailingAddress()
    {
        return $this->getConfigData('mailing_address');
    }
}