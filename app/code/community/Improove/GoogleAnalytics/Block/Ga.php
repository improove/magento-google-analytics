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
     * @deprecated after 1.4.1.1
     * self::_getOrdersTrackingCode()
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

    /**
     * Render regular page tracking javascript code
     * The custom "page name" may be set from layout or somewhere else. It must start from slash.
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._trackPageview
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gaq.html
     * @param string $accountId
     * @return string
     */
    protected function _getPageTrackingCode($accountId)
    {
        $pageName   = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && preg_match('/^\/.*/i', $pageName)) {
            $optPageURL = ", '{$this->jsQuoteEscape($pageName)}'";
        }
        return "var _gaq = [['_setAccount', '{$this->jsQuoteEscape($accountId)}'], ['_trackPageview'{$optPageURL}]];";
    }

    /**
     * Render information about specified orders and their items with currency conversion
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }

            $baseToGlobalRate = $order->getBaseToGlobalRate();

            $result[] = sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                $order->getIncrementId(), Mage::app()->getStore()->getFrontendName(), $order->getBaseGrandTotal()*$baseToGlobalRate,
                $order->getBaseTaxAmount()*$baseToGlobalRate, $order->getBaseShippingAmount()*$baseToGlobalRate,
                $this->jsQuoteEscape($address->getCity()),
                $this->jsQuoteEscape($address->getRegion()),
                $this->jsQuoteEscape($address->getCountry())
                );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice()*$baseToGlobalRate, $item->getQtyOrdered());
            }
            $result[] = "_gaq.push(['_trackTrans']);";
        }
        return implode("\n", $result);
    }

    /**
     * Render optimized asynchronous GA tracking snippet
     *
     * @link http://mathiasbynens.be/notes/async-analytics-snippet
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            return '';
        }
        $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        return '
<script>
    ' . $this->_getPageTrackingCode($accountId) . '
    (function(d, t) {
        var g = d.createElement(t),
            s = d.getElementsByTagName(t)[0];
        g.src = \'//www.google-analytics.com/ga.js\';
        s.parentNode.insertBefore(g, s);
    }(document, \'script\'));
    ' . $this->_getOrdersTrackingCode() . '
</script>
';
    }
}