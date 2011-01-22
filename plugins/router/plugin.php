<?php

namespace 'pixelpost\plugins\router';

use 'pixelpost';

/**
 * Base router for utltralite2. 
 *
 * Tracks Event :
 *
 * request.new
 *
 * Sends Event :
 *
 * request.api
 * request.admin
 * request.web 
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
class Plugin implements PluginInterface
{
    public static function on_request(Event $event)
    {
        // retreive the configuration
        $conf = Config::create();

        // get the url paramters, the Request class already split the url (using 
        // slashes) and get_params() is the array result of the split. 
        $urlParams = $event->request->get_params();

        // no parameter in the url, we add a virtual one
        if (count($urlParams) == 0) $urlParams[] = 'index';

        // prepare the event data (we just continu to pass the request class 
        // send by the 'request.new' event).
        $eventData = array('request' => $event->request);

        // make a choice between ADMIN, API, WEB. 
        // ADMIN and API base url are sent in the configuration file
        // other words is the WEB interface.
        switch (array_shift($urlParams))
        {
            case $conf->admin : Event::signal('request.admin', $eventData);
            case $conf->api   : Event::signal('request.api',   $eventData);
            default           : Event::signal('request.web',   $eventData);
        }

        // we order to stop processing of the event request.new by returning 
        // false
        return false;
    }

    public static function version()
    {
        return '0.0.1';
    }

    public static function install()
    {
        return true;
    }

    public static function uninstall()
    {
        return true;
    }

    public static function update()
    {
        return true;
    }    
    
    public static function register()
    {
        Event::register('request.new', __NAMESPACE__ . '::on_request');
    }
}
