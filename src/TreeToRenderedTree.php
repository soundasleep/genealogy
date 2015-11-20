<?php

namespace Genealogy;

class TreeToRenderedTree {
  function __construct(Tree $tree) {
    $this->tree = $tree;
  }

  function getRenderedTree() {
    $partners = $this->constructPartners();

    $nodes_to_show = array();

    // for each key person in the tree,
    foreach ($this->tree->getPeople() as $person) {
      if ($person->isKeyPerson()) {
        // we want to show this person
        $nodes_to_show[] = $person->getKey();

        // and all of their direct descendants and siblings and parents
        foreach ($partners as $partner) {
          if ($partner['left'] == $person->getKey() ||
              $partner['right'] == $person->getKey() ||
              in_array($person->getKey(), $partner['children'])) {

            $nodes_to_show[] = $partner['left'];
            $nodes_to_show[] = $partner['right'];

            foreach ($partner['children'] as $child) {
              $nodes_to_show[] = $child;
            }
          }
        }
      }
    }

    // now work out all the nodes that we want to connect
    $nodes_to_show = array_values(array_unique($nodes_to_show));

    print_r($partners);
    print_r($nodes_to_show);

    return new RenderedTree();
  }

  function constructPartners() {
    // construct sets of partners of (left, right, children)
    $partners = array();
    $found_partners = array();

    foreach ($this->tree->getPeople() as $person) {
      if (isset($found_partners[$person->getKey()])) {
        continue;
      }

      $married = array_merge($person->married(), $person->partners());
      if (count($married) > 1) {
        $spouse = $married[0]['person']->value()['person'];
        $partners[] = array(
          'left' => $person->getKey(),
          'right' => $spouse->getKey(),
          'children' => array(),
        );
        $found_partners[$person->getKey()] = true;
        $found_partners[$spouse->getKey()] = true;
      }
    }

    foreach ($this->tree->getPeople() as $person) {
      $mother = false;
      $father = false;

      if ($person->mother()) {
        $value = $person->mother()->value();
        if (isset($value['person'])) {
          $mother = $person->mother()->value()['person']->getKey();
        }
      }

      if ($person->father()) {
        $value = $person->father()->value();
        if (isset($value['person'])) {
          $father = $person->father()->value()['person']->getKey();
        }
      }

      foreach ($partners as $key => $partner) {
        if (($partner['left'] == $mother && $partner['right'] == $father) ||
            ($partner['right'] == $mother && $partner['left'] == $father)) {
          $partners[$key]['children'][] = $person->getKey();
        }
      }
    }

    return $partners;
  }
}
