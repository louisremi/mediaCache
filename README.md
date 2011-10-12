mediaCache
==========

mediaCache lets you put image, audio and video resources in a dynamic cache for offline use.  
You can now build a Twitter or Google Maps client using Open Web technologies only (read why [this was impossible](http://www.louisremi.com/2011/10/07/offline-web-applications-were-not-there-yet/) before). 

    var url = "http://gravatar.com/avatar/1d924ae6b834d2c43d313a94137ac6fe";
    
    // add a media to the cache while online
    mediaCache.cache( url, successCallback, errorCallback );
    
    // remove a resource from the cache at anytime
    mediaCache.remove( url );
    
    // get a resource from the cache while offline
    mediaCache.get( url, function( media ) {
        // media is an <img>, <audio> or <video> DOM element, based on the resource extension
        document.body.appendChild( media );
    });
    
    // The URI has to end with a file extension, if it's not the case, add a dummy parameter such as "x=.png" (see example above).
    // Once a URI has been cached, the exact same uri has to be used to "remove()" it or "get()" it from the cache.

How Does That Work?
-------------------

mediaCache is the combination of a server-side script (currently available in php) and a JS librarys:

* The server-side script generates iframes and unique cache manifest for each resource request, so that every resource resides in its own cache group and can be cached/removed at will.
* The JS library knows how to send requests to the server-side script. 

What Are The Limitations?
-------------------------

* Obviously the browser needs to be compatible with [Appcache](http://www.w3.org/TR/html5/offline.html).
* Your website and the server-side script need to reside on the same origin (e.g. http://subdomain.domain.tld), medias can be hosted anywhere (Yay!).

How can it be improved?
-----------------------

I'm looking for feedback regarding both the API and the logic.
I'd like to encourage people to port the server-side script to their favorite language.

Credits
-------

[@louis_remi](http://twitter.com/louis_remi)

License
-------

MIT