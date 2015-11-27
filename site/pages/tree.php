<?php

global $tree;

$custom_parings = Spyc::YAMLLoad(__DIR__ . "/../../tree/custom_parings.yml");

$renderer = new \Genealogy\TreeToRenderedTree($tree, $custom_parings);
$rendered_tree = $renderer->getRenderedTree();

page_header("Tree", "page_tree");
require_template("tree", array("render" => $rendered_tree->render()));
page_footer();

