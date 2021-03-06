<?php

namespace Genealogy;

class Fact {
  // array of source => value
  var $facts = array();

  function __construct($sources, $value) {
    if (!is_array($sources)) {
      $sources = array($sources);
    }
    foreach ($sources as $source) {
      $this->add($source, $value);
    }
  }

  function add($source, $value) {
    $this->facts[$source] = $value;
  }

  // get the highest fact source
  function source() {
    $keys = array_keys($this->facts);
    return $keys[0];
  }

  // get the highest fact value
  function value() {
    return $this->facts[$this->source()];
  }

  function __toString() {
    $value = $this->value();
    if (is_array($value)) {
      $s = array();
      foreach ($value as $key => $v) {
        $s[] = "$key = $v";
      }
      $value = "(" . implode(",", $s) . ")";
    }
    return get_class($this) . "[$value]";
  }
}
