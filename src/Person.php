<?php

namespace Genealogy;

class Person {
  var $data;
  var $key;
  var $name;

  function __construct($key, $yaml, $tree) {
    $defaults = array(
      'born' => array(),
      'married' => array(),
      'died' => array(),
      'occupation' => array(),
    );

    $yaml = array_merge($defaults, $yaml);

    $this->key = $key;
    $this->data = $yaml;
    $this->name = $yaml['name'];
    $this->tree = $tree;
  }

  function getKey() {
    return $this->key;
  }

  function resolveLinks() {
    // does nothing yet
  }

  function getName() {
    return $this->name;
  }

  function alsoKnownAs() {
    $known = array();
    foreach (array("aka", "now") as $key) {
      if (isset($this->data[$key])) {
        $known[] = $this->data[$key];
      }
    }
    return $known;
  }

  function born() {
    return $this->bornAt() || $this->bornIn();
  }

  function bornAt() {
    if (isset($this->data['born'])) {
      if (is_array($this->data['born'])) {
        return isset($this->data['born']['date']) ? $this->data['born']['date'] : false;
      } else {
        return $this->data['born'];
      }
    } else {
      return false;
    }
  }

  function bornIn() {
    if (isset($this->data['born']) && isset($this->data['born']['place'])) {
      return $this->data['born']['place'];
    } else {
      return false;
    }
  }

  function married() {
    $defaults = array(
      'name' => false,
      'date' => false,
      'place' => false,
    );

    $data = $this->data['married'];
    foreach ($data as $key => $values) {
      $data[$key] = array_merge($defaults, $data[$key]);
      if (isset($data[$key]['person'])) {
        $person = $this->tree->getPerson($data[$key]['person']);
        $data[$key]['person'] = $person;
        if ($person) {
          $data[$key]['name'] = $data[$key]['person']->getName();
        }
      }
    }
    return $data;
  }

  function mother() {
    return $this->personKey("mother");
  }

  function father() {
    return $this->personKey("father");
  }

  function siblings() {
    $siblings = array();

    if ($this->father() && $this->mother()) {
      foreach ($this->tree->getPeople() as $person) {
        if ($person->father() == $this->father() && $person->mother() == $this->mother()) {
          $siblings[] = array(
            'person' => $person,
          );
        }
      }
    }

    return $siblings;
  }

  function children() {
    $children = array();

    foreach ($this->tree->getPeople() as $person) {
      if ($person->father() == $this->person() || $person->mother() == $this->person()) {
        $children[] = array(
          'person' => $person,
        );
      }
    }

    return $children;
  }

  function personKey($key) {
    if (isset($this->data[$key])) {
      $person = $this->tree->getPerson($this->data[$key]);
      if ($person) {
        return $person->person();
      } else {
        return array(
          'name' => $this->data[$key],
        );
      }
    } else {
      return false;
    }
  }

  function person() {
    return array(
      'person' => $this,
    );
  }

  function getLinkName() {
    $s = array(
      $this->getName(),
    );
    if ($this->bornAt()) {
      $s[] = "(" . date("Y", strtotime($this->bornAt())) . ")";
    }
    return implode(" ", $s);
  }
}
