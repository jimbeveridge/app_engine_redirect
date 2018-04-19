<?php


if (isset($_SERVER['REQUEST_URI'])) {
  $redirect = new RedirectUrls();
  $url = $redirect->getRedirectUrl($_SERVER['REQUEST_URI']);
  if ($url != "") {
    $redirect->redirect($url);
  }
}

class RedirectUrls {

  public static $redirects = [
    # Equivalent .htaccess lines
    #>Redirect Permanent /Downloads https://storage.googleapis.com/example/Downloads
    #>Redirect Permanent /Manual http://example.atlassian.net/
    '/docs/Manual'     => 'http://example.atlassian.net/CAT-',
    '/Downloads'       => 'https://storage.googleapis.com/example/Downloads'
  ];

  public function getRedirectUrl($uri) {

    $req = parse_url($uri);
    if (!isset($req['path'])) {
	    return $uri;
    }

    foreach (self::$redirects as $key => $value) {
      $path = @$req['path'];
      $key_len = strlen($key);

      // Does the key exactly match the start or the request path?
      if (strncmp($key, $path, $key_len) != 0)
        continue;

      // Strip out the matching part and leave the remainder
      $path = substr($path, $key_len);
      if ($path === FALSE) $path = "";

      // The remainder must be empty or must start a new path component
      if ($path != "" && substr($path,0, 1) != "/")
        continue;

      $redirect = parse_url($value);

      // Malformed redirect URL?, or just a plain host?
      //if (!isset($redirect['path'])) $redirect['path'] = "/";

      $req['path'] = $redirect['path'] . $path;

      $overrides = [ 'scheme', 'host', 'query' ];
      foreach ($overrides as $override) {
        if (isset($redirect[$override])) {
          $req[$override] = $redirect[$override];
        }
      }

      break;
    }

    return self::unparseUrl($req);
  }

  private static function unparseUrl($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '//';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    #$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    #$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    #$pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    //return "$scheme$user$pass$host$port$path$query$fragment";
    return "$scheme$host$port$path$query$fragment";
  }

  public static function redirect($url) {
    header( "HTTP/1.1 301 Moved Permanently" );
    header( "Location: " . $url );
  }
}
