<?php

require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/functions.php");

// set up config
Openclerk\Config::merge(array(
  "site_name" => "genealogy",
  "absolute_url" => is_localhost() ? "http://localhost/genealogy/" : "http://example.com/",
  "display_errors" => is_localhost(),
));

// set up routes
\Openclerk\Router::addRoutes(array(
  ":page" => "pages/:page.php",
));

// set up pages
\Pages\PageRenderer::addTemplatesLocation(__DIR__ . "/../site/templates");
\Pages\PageRenderer::addStylesheet(\Openclerk\Router::urlFor("css/default.css"));
\Pages\PageRenderer::addJavascript("https://code.jquery.com/jquery-2.1.1.min.js");
\Pages\PageRenderer::addJavascript(\Openclerk\Router::urlFor("js/default.js"));

require(__DIR__ . "/tree.php");
