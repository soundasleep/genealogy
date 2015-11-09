<?php

function is_localhost() {
  return $_SERVER['SERVER_NAME'] === "localhost";
}

function page_header($title, $id = "", $arguments = array()) {
  $arguments['title'] = $title;
  $arguments['id'] = $id;
  \Pages\PageRenderer::header($arguments);
}

function page_footer($arguments = array()) {
  \Pages\PageRenderer::footer($arguments);
}

function require_template($template_id, $arguments = array()) {
  \Pages\PageRenderer::requireTemplate($template_id, $arguments);
}

function link_to($url, $text = false) {
  if ($text === false) {
    return link_to($url, $url);
  }
  if ($url == "index") {
    $url = "./";
  }
  return "<a href=\"" . htmlspecialchars($url) . "\">" . htmlspecialchars($text) . "</a>";
}
