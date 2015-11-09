<?php

/**
 * Basic router implementation
 */

require(__DIR__ . "/../inc/global.php");

$path = require_get("path", "home");
if ($path == "index") {
  $path = "home";
}

try {
  \Openclerk\Router::process($path);
} catch (\Openclerk\RouterException $e) {
  header("HTTP/1.0 404 Not Found");
  echo "<h1>" . htmlspecialchars($e->getMessage()) . "</h1>\n\n";
  if (\Openclerk\Config::get('display_errors', false)) {
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
  }
}
