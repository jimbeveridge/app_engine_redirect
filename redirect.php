<?php

# Copyright 2018 Google LLC.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     https://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

if (isset($_SERVER['REQUEST_URI'])) {
  $redirect = new RedirectUrls();
  $url = $redirect->getRedirectUrl($_SERVER['REQUEST_URI']);
  if ($url != "") {
    $redirect->redirect($url);
  }
}

class RedirectUrls {

  # The keys on the left will match the beginning of any incoming URL.
  # The remainder will be added to the URL on the right. If the URL on
  #  the right includes a scheme or host, that will be replaced.
  # Given the example redirects below:
  # http://www.trilobyte.com/docs/catid/56 would become http://example.atlassian.net/CAT/56
  # http://www.example.com/docs/rel_catid/56 would become http://www.example.com/CAT-56
  # The code is smart enough to NOT match /docs/catid5/val based on the first key below.
  # There must either be a slash or nothing appearing after the key.
  public static $redirects = [
    '/docs/catid'     => 'http://example.atlassian.net/CAT/',
    '/docs/rel_catid'     => '/CAT-',
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
      $suffix = substr($path, $key_len);
      if ($suffix === FALSE) $suffix = "";

      // The remainder must be empty or must start a new path component
      // This is so we don't accidentally match substrings.
      if ($suffix != "" && substr($suffix,0, 1) != "/")
        continue;

      $parsed_location = parse_url($value);

      $req['path'] = $parsed_location['path'] . substr($suffix, 1);

      $overrides = [ 'scheme', 'host', 'query' ];
      foreach ($overrides as $override) {
        if (isset($parsed_location[$override])) {
          $req[$override] = $parsed_location[$override];
        }
      }
      print_r($req);

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
