<?php

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Not Found Redirector
 *
 * Redirects NotFounds to the Search
 *
 * @package   OstNotFoundRedirector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstNotFoundRedirector\Listeners;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;

class Frontend implements SubscriberInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * Frontend constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
        ];
    }



    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $subject */
        $subject = $args->getSubject();

        $request = $subject->Request();
        $response = $subject->Response();

        foreach (explode("\n", $this->config['disallowedParts']) as $part) {
            if (strpos($request->getRequestUri(), $part) !== false) {
                return;
            }
        }

        if (404 === $response->getHttpResponseCode() && strtolower($request->getControllerName()) !== 'error') {
            $url_original = $request->getRequestUri();
            $url_original = str_replace(['=', '?'], '', $url_original);

            $url_parts = array_filter(array_reverse(explode('/', $url_original)));
            $redirect = '/search?sSearch=' . implode('+', $url_parts);

            $response->setRedirect($request->getBaseUrl() . $redirect, 301);
        }
    }
}