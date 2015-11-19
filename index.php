<?php
  require_once('request.php');

  header('Content-Type: text/plain; charset=UTF-8');

  $url = "https://jigsaw.w3.org/HTTP/300/302.html";

  $xhr = request($url);
  $xhr = json_encode($xhr,
                     0
                     | JSON_HEX_APOS /*           All ' are converted to \u0027.                                                  (Available since PHP 5.3.0). */
                     | JSON_HEX_QUOT /*           All " are converted to \u0022.                                                  (Available since PHP 5.3.0)*/
                     | JSON_HEX_AMP /*            All &#38;#38;s are converted to \u0026.                                         (Available since PHP 5.3.0). */
                     | JSON_HEX_TAG /*            All &lt; and &gt; are converted to \u003C and \u003E.                           (Available since PHP 5.3.0). */
                     | JSON_NUMERIC_CHECK /*      Encodes numeric strings as numbers.                                             (Available since PHP 5.3.3). */
                     | JSON_UNESCAPED_UNICODE /*  Encode multibyte Unicode characters literally (default is to escape as \uXXXX)  (Available since PHP 5.4.0). */
                     | JSON_PRETTY_PRINT /*       Use whitespace in returned data to format it                                    (Available since PHP 5.4.0). */
  );

  echo $xhr;
