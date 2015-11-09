<?php

namespace Genealogy;

class Tree {
  var $people = array();

  static function load($yaml) {
    $tree = new Tree();
    foreach ($yaml as $person => $info) {
      $person = new Person($person, $info, $tree);
      $tree->addPerson($person);
    }

    $tree->resolveLinks();

    return $tree;
  }

  function addPerson(Person $p) {
    $this->people[$p->getKey()] = $p;
  }

  function resolveLinks() {
    foreach ($this->people as $person) {
      $person->resolveLinks($this);
    }
  }

  function getPeople() {
    return $this->people;
  }

  function getPerson($key) {
    return isset($this->people[strtolower($key)]) ? $this->people[strtolower($key)] : false;
  }
}
