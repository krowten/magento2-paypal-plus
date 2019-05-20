<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Iways\PayPalPlus\Model\System\Config\Source;
/**
 * PayPal Api Mode resource class
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Live string
     */
    const LIVE = 'live';

    /**
     * Sandbox string
     */
    const SANDBOX = 'sandbox';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LIVE, 'label' => __('Live')],
            ['value' => self::SANDBOX, 'label' => __('Sandbox')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SANDBOX => __('Sandbox'),
            self::LIVE => __('Live'),
        ];
    }
}
