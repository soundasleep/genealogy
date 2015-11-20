<?php

$rendered_tree = new \Genealogy\RenderedTree();

page_header("Tree", "page_tree");
require_template("tree", array("render" => $rendered_tree->render()));
page_footer();

