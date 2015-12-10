<?php

// load the yaml
$tree = new Genealogy\Tree();

$files = array('family', 'documented', 'internet');
foreach ($files as $key) {
  $yml = Spyc::YAMLLoad(__DIR__ . "/../tree/$key.yml");

  // convert into objects
  $tree->load($yml, $key);
}

function fact($fact) {
  return source($fact->source(), htmlspecialchars($fact->value()));
}

function source($source, $value) {
  return "<span class=\"fact " . htmlspecialchars($source) . "\" title=\"Source: " . htmlspecialchars($source) . "\">" . $value . "</span>";
}
