<?php

namespace Genealogy;

class Tree {
  var $people = array();

  function load($yaml, $source_key) {
    foreach ($yaml as $key => $info) {
      $person = $this->getPerson($key);
      if ($person) {
        $person->merge($info, $source_key);
      } else {
        $person = new Person($key, $info, $this, $source_key);
        $this->addPerson($person);
      }
    }

    $this->resolveLinks();

    return $this;
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
    if (!is_string($key)) {
      throw new \InvalidArgumentException("'$key' is not a string key.");
    }

    return isset($this->people[strtolower($key)]) ? $this->people[strtolower($key)] : false;
  }
}
