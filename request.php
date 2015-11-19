<?php
  function request($url, $is_head = false) {
    $curl_handle = curl_init();

    if ($is_head) {
      curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'HEAD');
      curl_setopt($curl_handle, CURLOPT_NOBODY, true);
    }


    curl_setopt_array($curl_handle, [
      CURLOPT_URL              => $url// -------------------------------------------- set full target URL.
      , CURLOPT_CONNECTTIMEOUT => 30 // ---------------------------------------- timeout on connect, in seconds
      , CURLOPT_TIMEOUT        => 30 // ---------------------------------------- timeout on response, in seconds
      , CURLOPT_BUFFERSIZE     => 2048 // -------------------------------------------- smaller buffer-size for proxies.
      , CURLOPT_HEADER         => true // -------------------------------------------- return headers too
      , CURLINFO_HEADER_OUT    => true // -------------------------------------------- to use $rh = curl_getinfo($curl_handle); var_dump($rh['request_header']);
      , CURLOPT_RETURNTRANSFER => true // -------------------------------------------- return as string
      //, CURLOPT_FAILONERROR    => true // -------------------------------------------- don't fetch error-page's content (500, 403, 404 pages etc..)
      , CURLOPT_SSL_VERIFYHOST => false // ------------------------------------------- don't verify ssl
      , CURLOPT_SSL_VERIFYPEER => false // ------------------------------------------- don't verify ssl
      , CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4 // ------------------------------- force IPv4 (instead of IPv6)
      , CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1// ---------------------------- force HTTP 1.1

      /* redirects */
      , CURLOPT_FOLLOWLOCATION => true
      , CURLOPT_MAXREDIRS      => 5
      , CURLOPT_HTTPHEADER     => call_user_func(function () {
        $headers = [
          "Accept"                      => "*/*"
          , "Accept-Language"           => "en,en-US;q=0.8"
          , "Connection"                => "keep-alive"
          , "Content-Type"              => "text/plain; charset=utf-8"
          , "Cache-Control"             => "no-cache"
          , "Pragma"                    => "no-cache"
          , "User-Agent"                => "Mozilla/5.0 Chrome"
          , "Referer"                   => "http://www.google.com/"
          , "DNT"                       => "1"
          , "Upgrade-Insecure-Requests" => "1"
        ];

        $headers = array_map(function ($key, $value) {
          return $key . ': ' . $value;
        }, array_keys($headers), array_values($headers));

        return $headers;
      })

    ]);


    $response = curl_exec($curl_handle);
    $info = curl_getinfo($curl_handle);

    $err_num = curl_errno($curl_handle);
    $err_str = curl_error($curl_handle);

    @curl_close($curl_handle);
    unset($curl_handle);

    $num_of_redirects = isset($info["redirect_count"]) ? $info["redirect_count"] + 2 :
      /* heuristics - try to find parts that starts with "header like" string, add +1 for body */
      call_user_func(function () use ($response) {
        $response = explode("\r\n\r\n", $response); //candidates

        $num_of_redirects = array_reduce($response, function ($value, $item) {
          return $value + ((0 === mb_strpos($item, "HTTP/", 0)) ? 1 : 0); //starts with "HTTP/" ---> it is a redirect header.
        }, 0);

        return $num_of_redirects + 1 /* the +1 is for the body */
          ;
      });


    $response = explode("\r\n\r\n", $response, $num_of_redirects);
    unset($num_of_redirects);

    $response_body = array_pop($response);

    $response_header_groups = $response; //now contains just a bunch of strings.
    unset($response); // *avoid confusing names..

    $response_header_groups = array_map(function ($header_group) { //reformat string to associative array for all header-groups.
      $header_group = trim($header_group);

      $headers = [];
      $lines = explode("\r\n", $header_group);
      foreach ($lines as $index => $line) {
        $line = explode(": ", $line, 2); //limit to one match.

        if (1 === count($line)) //probably lines such as "HTTP/1.1 302 Found" which does not have ": " delimiter, the key will be the [0] (index) using unshift.
          array_unshift($line, $index); //fix "key" to be the index, in case there is no ': ' delimiter.

        $key = $line[0];
        $value = $line[1];

        $headers[ $key ] = $value;

      }

      return $headers;

    }, $response_header_groups);

    $request_headers = isset($info["request_header"]) ? call_user_func_array(function ($request_headers) {
      $request_headers = trim($request_headers);

      $headers = [];
      $lines = explode("\r\n", $request_headers);
      foreach ($lines as $index => $line) {
        $line = explode(": ", $line, 2); //limit to one match.

        if (1 === count($line)) //probably lines such as "HTTP/1.1 302 Found" which does not have ": " delimiter, the key will be the [0] (index) using unshift.
          array_unshift($line, $index); //fix "key" to be the index, in case there is no ': ' delimiter.

        $key = $line[0];
        $value = $line[1];

        $headers[ $key ] = $value;

      }

      return $headers;
    }, [$info["request_header"]])
      : [];

    return [
      'info'     => $info
      ,
      'request'  => [
        'headers' => $request_headers
      ]
      ,
      'response' => [
        'headers' => $response_header_groups
        , 'body'  => $response_body
      ]
      ,
      'error'    => [
        'num'   => $err_num
        , 'str' => $err_str
      ]

    ];
  }

?>