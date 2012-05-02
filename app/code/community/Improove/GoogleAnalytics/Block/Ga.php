<?php

/**
 * GoogleAnalitics Page Block
 *
 * @category   Mage
 * @package    Mage_GoogleAnalytics
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Improove_GoogleAnalytics_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Retrieve Order Data HTML with currency conversion
     *
     * @return string
     */
    public function getOrderHtml()
    {

        $order = $this->getOrder();
        if (!$order) {
            return '';
        }

        if (!$order instanceof Mage_Sales_Model_Order) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!$order) {
            return '';
        }

        $address = $order->getBillingAddress();

        $baseToGlobalRate = $order->getBaseToGlobalRate(); // >= 1

        $html  = '<script>' . "\n";
        $html .= "//<![CDATA[\n";
        $html .= '_gaq.push(["_addTrans",';
        $html .= '"' . $order->getIncrementId() . '",';
        $html .= '"' . $order->getAffiliation() . '",';
        $html .= '"' . $order->getBaseGrandTotal()*$baseToGlobalRate . '",';
        $html .= '"' . $order->getBaseTaxAmount()*$baseToGlobalRate . '",';
        $html .= '"' . $order->getBaseShippingAmount()*$baseToGlobalRate . '",';
        $html .= '"' . $this->jsQuoteEscape($address->getCity(), '"') . '",';
        $html .= '"' . $this->jsQuoteEscape($address->getRegion(), '"') . '",';
        $html .= '"' . $this->jsQuoteEscape($address->getCountry(), '"') . '"';
        $html .= ']);' . "\n";

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $html .= '_gaq.push(["_addItem",';
            $html .= '"' . $order->getIncrementId() . '",';
            $html .= '"' . $this->jsQuoteEscape($item->getSku(), '"') . '",';
            $html .= '"' . $this->jsQuoteEscape($item->getName(), '"') . '",';
            $html .= '"' . $item->getCategory() . '",';
            $html .= '"' . $item->getBasePrice()*$baseToGlobalRate . '",';
            $html .= '"' . $item->getQtyOrdered() . '"';
            $html .= ']);' . "\n";
        }

        $html .= '_gaq.push(["_trackTrans"]);' . "\n";
        $html .= '//]]>';
        $html .= '</script>';

        return $html;
    }
}