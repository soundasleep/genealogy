<?php

// load the yaml
$tree = Spyc::YAMLLoad(__DIR__ . "/../tree/family.yml");

// convert into objects
$tree = Genealogy\Tree::load($tree);
