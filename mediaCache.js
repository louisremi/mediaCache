(function( w, d, l, e ) {
"use strict";

// appcache feature detection
if ( !( "applicationCache" in w ) ) {
	return;
}

var iframe = d.createElement( "iframe" ),
	uncacheASAP = "_uncacheASAP";

iframe.style.display = "none";

if ( !( uncacheASAP in l ) ) {
	l[uncacheASAP] = "";
}

w.mediaCache = {
	// add a resource to the cache
	cache: function( uri, successCb, errorCb ) {
		createIframe( uri, "cache", successCb, errorCb );
	},
	// return a cached resource as its corresponding DOM element (<img>, <audio> or <video>)
	get: function( uri, successCb, errorCb ) {
		var f = createIframe( uri, "cache" ),
			self = this,
			timeout;

		timeout = setTimeout(function() {
			d.body.removeChild( f );
			!errorCb || errorCb();
		}, self.getTimeout);

		f.contentWindow.onmedia = function( media ) {
			d.body.removeChild( f );
			clearTimeout( timeout );
			!successCb || successCb( media );
		};
	},
	// remove a resource from the cache
	// this can be used offline but removePending() has to be called once online
	remove: function( uri ) {
		// keep track of resources to be removed
		// if we fail at deleting one and don't keep track
		// the application will leak in the appcache!!!
		l[uncacheASAP] += "|" + e( uri );
		if ( navigator.onLine ) {
			createRemoveIframe( uri );
		}
	},
	// once online, this effectively removes from the cache uris passed to remove() while offline
	removePending: function() {
		if ( navigator.onLine && l[uncacheASAP] ) {
			var uris = l[uncacheASAP].slice(1).split("|"),
				i = uris.length;

			while ( i-- ) {
				createRemoveIframe( uris[i] );
			}
		}
	},
	serverPath: "/mediaCache.php",
	removeTimeout: 5000,
	getTimeout: 250
};

function createIframe( uri, action, successCb, errorCb ) {
	var f = iframe.cloneNode( false );
	f.src = mediaCache.serverPath + "?action=" + action + "&uri=" + e( uri );
	d.body.appendChild( f );

	f.contentWindow.oncomplete = function( success ) {
		d.body.removeChild( f );
		success ?
			!successCb || successCb():
			!errorCb || errorCb();
	}
	return f;
}

function createRemoveIframe( uri ) {
	var f = createIframe( uri, "remove", function() {
			clearTimeout( timeout );
		}),
		timeout;

	timeout = setTimeout(function() {
		d.body.removeChild( f );
	}, mediaCache.removeTimeout);
}

// cleanup garbage at least on every page load
mediaCache.removePending();

})( window, document, localStorage, encodeURIComponent );