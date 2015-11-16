<?php

namespace Genealogy;

class Person {
  var $data = array();
  var $key;

  function __construct($key, $yaml, $tree, $source_key) {
    if (!$key) {
      throw new \InvalidArgumentException("Invalid blank key '$key'");
    }

    $this->key = $key;
    $this->data = $this->mergeRecursively(array(), $source_key, $yaml);
    $this->tree = $tree;
  }

  function mergeRecursively($result, $source_key, $array) {
    if (!is_array($array)) {
      throw new \InvalidArgumentException("'$array' is not an array");
    }

    foreach ($array as $key => $value) {
      if (isset($result[$key])) {
        if (is_array($result[$key])) {
          $result[$key] = $this->mergeRecursively($result[$key], $source_key, $value);
        } else {
          $result[$key]->add($source_key, $value);
        }
      } else {
        if (is_array($value)) {
          $result[$key] = $this->mergeRecursively(array(), $source_key, $value);
        } else {
          $result[$key] = new Fact($source_key, $value);
        }
      }
    }
    return $result;
  }

  function merge($yaml, $source_key) {
    $this->data = $this->mergeRecursively($this->data, $source_key, $yaml);
  }

  function getKey() {
    return $this->key;
  }

  function resolveLinks() {
    // does nothing yet
  }

  function getName() {
    return $this->data['name'];
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

    $data = isset($this->data['married']) ? $this->data['married'] : array();
    foreach ($data as $key => $values) {
      $data[$key] = array_merge($defaults, $data[$key]);
      if (isset($data[$key]['person'])) {
        $person = $this->tree->getPerson($data[$key]['person']->value());
        // TODO add Fact wrapper here
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
        if ($person->father() == $this->father()
              && $person->mother() == $this->mother()
              && $person->person() != $this->person()) {
          $sources = array(
            $person->mother()->source(),
            $person->father()->source(),
          );
          $siblings[] = new Fact($sources, $person->person());
        }
      }
    }

    return $siblings;
  }

  function children() {
    $children = array();

    foreach ($this->tree->getPeople() as $person) {
      if (($person->father() == $this->person()
          || $person->mother() == $this->person())
          && $person->person() != $this->person()) {
        $children[] = array(
          'person' => $person,
        );
      }
    }

    return $children;
  }

  function personKey($key) {
    if (isset($this->data[$key])) {
      $source = $this->data[$key]->source();
      $person = $this->tree->getPerson($this->data[$key]->value());
      if ($person) {
        return new Fact($source, $person->person());
      } else {
        return new Fact($source, array('name' => $this->data[$key]));
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
      $this->getName()->value(),
    );
    if ($this->bornAt()) {
      $s[] = "(" . date("Y", strtotime($this->bornAt()->value())) . ")";
    }
    return implode(" ", $s);
  }
}
