<?php


namespace Iways\PayPalPlus\Model\System\Config\Source;


class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{
    const SALE = \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;

    const AUTHORIZATION = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;

    public function toOptionArray()
    {
        $return = [];
        foreach ($this->toArray() as $value => $label) {
            $return[] = ['value' => $value, 'label' => $label];
        }
        return $return;
    }

    public function toArray()
    {
        return [
            self::AUTHORIZATION => __('Authorization'),
            self::SALE => __('Sale'),
        ];
    }
}
