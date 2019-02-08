<?php declare(strict_types=1);

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Not Found Redirector
 *
 * @package   OstNotFoundRedirector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstNotFoundRedirector\Listeners\Controllers;

use Enlight_Controller_Action as Controller;
use Enlight_Event_EventArgs as EventArgs;

class Frontend
{
    /**
     * @var array
     */
    private $config;

    /**
     * ...
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        // set params
        $this->config = $config;
    }

    /**
     * ...
     *
     * @param EventArgs $arguments
     */
    public function onPostDispatch(EventArgs $arguments)
    {
        /** @var Controller $subject */
        $subject = $arguments->getSubject();

        // get parameters
        $request = $subject->Request();
        $response = $subject->Response();

        // check for disallowed parts
        foreach (explode("\n", (string) $this->config['disallowedParts']) as $part) {
            if (strpos(strtolower($request->getRequestUri()), strtolower($part)) !== false) {
                return;
            }
        }

        // check for invalid 404 not-found response
        if ($response->getHttpResponseCode() === 404 && strtolower($request->getControllerName()) !== 'error') {
            // get the url and split it to get the paramters
            $urlOriginal = $request->getRequestUri();
            $urlOriginal = str_replace(['=', '?'], '', $urlOriginal);
            $urlParts = array_filter(array_reverse(explode('/', $urlOriginal)));

            // remove "artikel" from ".de/artikel/123456" to search for the article number only
            if ((count($urlParts) > 1) && $urlParts[count($urlParts) - 1] === 'artikel') {
                $urlParts = array_slice($urlParts, 0, count($urlParts) - 1);
            }

            // set redirect url
            $redirect = '/search?sSearch=' . implode('+', $urlParts);

            // and redirect
            $response->setRedirect($request->getBaseUrl() . $redirect, 301);
        }
    }
}
