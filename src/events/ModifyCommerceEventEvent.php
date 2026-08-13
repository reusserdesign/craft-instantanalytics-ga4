<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalyticsGa4\events;

use Br33f\Ga4\MeasurementProtocol\Dto\Event\AbstractEvent;
use yii\base\Event;

/**
 * Fired before a Commerce-derived analytics event is queued for sending, so
 * that projects can attach their own metadata to it, or suppress it entirely.
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     4.0.4
 */
class ModifyCommerceEventEvent extends Event
{
    // Public Properties
    // =========================================================================

    /**
     * @var AbstractEvent the GA4 event that is about to be queued. Add custom
     * parameters to it with `setParamValue()`, which takes the parameter name
     * exactly as GA4 should receive it.
     */
    public AbstractEvent $analyticsEvent;

    /**
     * @var mixed the Commerce element the event was built from. This is an
     * `Order` for the cart/checkout events, a `LineItem` for the add/remove
     * from cart events, and a `Product` or `Variant` for the item events.
     */
    public mixed $source = null;

    /**
     * @var bool whether the event should be queued for sending. Set this to
     * `false` to drop the event.
     */
    public bool $isValid = true;
}
