<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalyticsGa4\variables;

use Br33f\Ga4\MeasurementProtocol\Dto\Event\BaseEvent;
use Craft;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\helpers\Template;
use nystudio107\instantanalyticsGa4\ga4\Analytics;
use nystudio107\instantanalyticsGa4\ga4\events\PageViewEvent;
use nystudio107\instantanalyticsGa4\helpers\Analytics as AnalyticsHelper;
use nystudio107\instantanalyticsGa4\InstantAnalytics;
use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;
use Throwable;
use Twig\Markup;
use yii\base\Exception;

/**
 * Instant Analytics Variable
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class InstantAnalyticsVariable implements ViteVariableInterface
{
    use ViteVariableTrait;

    // Public Methods
    // =========================================================================

    /**
     * Get a PageView Event
     *
     * @param string $url
     * @param string $title
     *
     * @return PageViewEvent
     */
    public function pageViewEvent(string $url = '', string $title = ''): PageViewEvent
    {
        return InstantAnalytics::$plugin->ga4->getPageViewEvent($url, $title);
    }

    /**
     * Get a simple event
     *
     * @param string $eventName
     * @return BaseEvent
     */
    public function simpleEvent(string $eventName = ''): BaseEvent
    {
        return InstantAnalytics::$plugin->ga4->getSimpleEvent($eventName);
    }

    /**
     * Return the GA4 Analytics object
     *
     * @return Analytics
     */
    public function ga4(): Analytics
    {
        return InstantAnalytics::$plugin->ga4->getAnalytics();
    }

    /**
     * @param Product|Variant|null $productVariant the Product or Variant
     */
    public function addCommerceProductView($productVariant = null): void
    {
        $this->collect(__METHOD__, static function() use ($productVariant) {
            InstantAnalytics::$plugin->commerce->addCommerceProductImpression($productVariant);
        });
    }

    /**
     * @param Product|Variant|null $productVariant the Product or Variant
     * @param string $listName
     */
    public function addCommerceProductSelect($productVariant = null, string $listName = 'default'): void
    {
        $this->collect(__METHOD__, static function() use ($productVariant, $listName) {
            InstantAnalytics::$plugin->commerce->addCommerceProductSelect($productVariant, $listName);
        });
    }

    /**
     * Send a begin_checkout event for the given cart
     *
     * @param ?Order $cart
     */
    public function beginCheckout(?Order $cart = null): void
    {
        $this->collect(__METHOD__, static function() use ($cart) {
            InstantAnalytics::$plugin->commerce->triggerBeginCheckoutEvent($cart);
        });
    }

    /**
     * Send a view_cart event for the given cart
     *
     * @param ?Order $cart
     */
    public function viewCart(?Order $cart = null): void
    {
        $this->collect(__METHOD__, static function() use ($cart) {
            InstantAnalytics::$plugin->commerce->triggerViewCartEvent($cart);
        });
    }

    /**
     * Send an add_shipping_info event for the given cart
     *
     * @param ?Order $cart
     * @param ?string $shippingTier
     */
    public function addShippingInfo(?Order $cart = null, ?string $shippingTier = null): void
    {
        $this->collect(__METHOD__, static function() use ($cart, $shippingTier) {
            InstantAnalytics::$plugin->commerce->triggerAddShippingInfoEvent($cart, $shippingTier);
        });
    }

    /**
     * Send an add_payment_info event for the given cart
     *
     * @param ?Order $cart
     * @param ?string $paymentType
     */
    public function addPaymentInfo(?Order $cart = null, ?string $paymentType = null): void
    {
        $this->collect(__METHOD__, static function() use ($cart, $paymentType) {
            InstantAnalytics::$plugin->commerce->triggerAddPaymentInfoEvent($cart, $paymentType);
        });
    }

    /**
     * Get a PageView tracking URL
     *
     * @param $url
     * @param $title
     *
     * @return Markup
     * @throws Exception
     */
    public function pageViewTrackingUrl($url, $title): Markup
    {
        return Template::raw(AnalyticsHelper::getPageViewTrackingUrl($url, $title));
    }

    /**
     * Get an Event tracking URL
     *
     * @param string $url
     * @param string $eventName
     * @param array $params
     * @return Markup
     * @throws Exception
     */
    public function eventTrackingUrl(
        string $url,
        string $eventName = '',
        array  $params = [],
    ): Markup {
        return Template::raw(AnalyticsHelper::getEventTrackingUrl($url, $eventName, $params));
    }

    // Private Methods
    // =========================================================================

    /**
     * Collect an analytics event, without letting a failure take down the page
     * that is being rendered.
     *
     * These helpers are called from Twig mid-render, so anything they throw
     * surfaces as a 500 for the visitor. Losing an analytics event is always
     * preferable to losing the page, so failures are logged and swallowed —
     * except in devMode, where failing loudly is what you want.
     *
     * @param string $method the calling method, used as the log category
     * @param callable $fn
     */
    private function collect(string $method, callable $fn): void
    {
        try {
            $fn();
        } catch (Throwable $e) {
            Craft::error($e, $method);

            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }
        }
    }
}
