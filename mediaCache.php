<?php

if ( !isset( $_GET['uri'] ) || $_GET['uri'] === '' ) {
	APIError('uri missing');
}

$decodeduri = $_GET['uri'];
$encodeduri = urlencode( $_GET['uri'] );

ob_start();

// manifest requested
if ( isset( $_GET['manifest'] ) ) {

	$value = $_COOKIE[ str_replace( '.', '_', $decodeduri ) ];

	// remove
	if ( $value === 'obsolete' ) {
		// 404 or 410 makes cache obsolete
		header('HTTP/1.0 410 Gone');

		// the cookie has to be removed
		setcookie( $encodeduri, NULL, -1 );

	// cache
	} else {
		// if the value starts with '#', this is a 2nd request (see step 24 of #downloading-or-updating-an-application-cache)
		// exact same manifest will have to be resent (although 304 would be enough for Chrome)
		$request2 = strpos( $value, '#' ) === 0;

		header('Content-type: text/cache-manifest');

		// a uid is added to the manifest to make sure the cache is refreshed
		$uniqid = $request2 ? $value : uniqid('#');

		// save the uid in a cookie so that we can distinguish 2nd request
		// the cookie is *always* deleted once the update process is completed, see line 122
		setcookie( $encodeduri, $uniqid );

		echo "CACHE MANIFEST\n";
		echo "$uniqid\n";
		echo $decodeduri;
	}

	// prevent manifest to be cached by proxies
	header('Cache-control: max-age=0');

// iframe requested
} else {

	echo "<!DOCTYPE html>\n";
	echo "<html manifest='".array_pop( explode ( '/', __FILE__ ) )."?manifest&uri=$encodeduri'>\n";
	echo "<body>\n";

	switch ( $_GET['action'] ) {
		case 'remove':
			setcookie( $encodeduri, 'obsolete' );
			echo "<script>\n";
			echo "var a = applicationCache, l = 'addEventListener';\n";
			// chrome fires error instead of obsolete event, listen to both.
			echo "a[l]( 'obsolete', removed );\n";
			echo "a[l]( 'error', removed );\n";
			echo "function removed() {\n";
			echo   "localStorage._uncacheASAP = (localStorage._uncacheASAP + '|').split('|$encodeduri|' ).join('|').slice(0,-1);\n";
			echo   "oncomplete(1);\n";
			echo "};\n";
			echo "</script>\n";
			break;

		case 'cache':

      // mime_content_type can only be used with local files
			$extension = strtolower( array_pop( explode( '.', $decodeduri ) ) );

      // feel free to add other extensions below and consider contributing your changes to the project 
			switch ( $extension ) {

				case 'mp3':
				case 'mp4a':
				case 'oga':
				case 'ogg':
				case 'wav':
				case 'weba':
					echo "<audio id='media' src='$decodeduri'></audio>\n";
					break;

				case 'avi':
				case 'flv':
				case 'mov':
				case 'mp4':
				case 'mpeg':
				case 'ogv':
				case 'webm':
					echo "<video id='media' src='$decodeduri'></video>\n";
					break;

				case 'apng':
				case 'bmp':
				case 'gif':
				case 'ico':
				case 'jpeg':
				case 'jpg':
				case 'png':
				case 'svg':
				case 'webp':
					echo "<img id='media' src='$decodeduri' />\n";
					break;

				default:
					APIError('uri has non-media extension, '.$extension);
					break;
			}

			echo "<script>\n";
			echo "var a = applicationCache, l = 'addEventListener';\n";
			echo "a[l]( 'cached', cached );\n";
			echo "a[l]( 'updateready', cached );\n";
			echo "a[l]( 'error', function() { cached( !!0 ); } );\n";
			echo "function cached( e ) {\n";
			echo   "document.cookie = '$encodeduri=;';\n";
			echo   "oncomplete( e );\n";
			echo "}\n";
			echo "if ( 'onmedia' in window ) {\n";
			echo   "var media = document.getElementById( 'media' ); media.id = '';\n";
			echo   "onmedia( media );\n";
			echo "}\n";
			echo "</script>\n";
			break;

		default:
			APIError('action missing');
			break;
	}

	echo "</body>\n";
	echo "</html>";
}

ob_flush();

function APIError ( $msg ) {
  ob_clean();
  exit('Malformed API request: '.$msg);
}

?>