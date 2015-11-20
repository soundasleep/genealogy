<?php

global $tree;

$renderer = new \Genealogy\TreeToRenderedTree($tree);
$rendered_tree = $renderer->getRenderedTree();

page_header("Tree", "page_tree");
require_template("tree", array("render" => $rendered_tree->render()));
page_footer();

