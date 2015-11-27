<?php

namespace Genealogy;

class TreeToRenderedTree {
  function __construct(Tree $tree, $custom_parings = array()) {
    $this->tree = $tree;
    $this->custom_parings = $custom_parings;
  }

  function getRenderedTree() {
    // get all the nodes (keys) that we want to display
    $nodes_to_display = $this->getKeysThatWeWantToDisplay();

    // get all marriage connections
    $marriages = $this->getAllMarriageConnections($nodes_to_display);

    // get all children
    $children = $this->getAllChildrenConnections($nodes_to_display);

    // calculate tree depths
    $depths = $this->calculateDepths($children, $marriages, $nodes_to_display);

    $custom_parings = $this->customParings();

    // calculate the x-offsets for each node
    $offsets = $this->calculateOffsets($children, $marriages, $depths, $custom_parings);

    // now we can make a RenderedTree!
    return $this->generateRenderedTree($marriages, $children, $depths, $offsets);
  }

  function generateRenderedTree($marriages, $children, $depths, $offsets) {
    $tree = new RenderedTree();

    // nodes
    foreach ($depths as $person => $y) {
      $x = $offsets[$person];
      $tree->nodes[] = array(
        'x' => $x,
        'y' => $y,
        'text' => $person,
        'person' => $this->tree->getPerson($person),
      );
    }

    // double_lines between marriages
    foreach ($marriages as $marriage) {
      $start_x = $offsets[$marriage[0]];
      $end_x = $offsets[$marriage[1]];
      $y = $depths[$marriage[0]];

      $tree->double_lines[] = array(
        'start x' => $start_x,
        'end x' => $end_x,
        'y' => $y,
        'title' => "marriage between ${marriage[0]} and ${marriage[1]}"
      );
    }

    // lines between children
    foreach ($children as $childs) {
      $child = $childs[0];
      $parents = $childs[1];

      if (count($parents) == 2) {
        // two parents

        // a line between two parents
        $start_x = $offsets[$parents[0]];
        $end_x = $offsets[$parents[1]];
        $y = $depths[$parents[0]];

        $tree->lines[] = array(
          'start x' => $start_x,
          'end x' => $end_x,
          'start y' => $y,
          'end y' => $y,
        );

        // a line down to the next depth (+1)
        $x = ($offsets[$parents[0]] + $offsets[$parents[1]]) / 2;
        $start_y = $depths[$parents[0]];
        $end_y = $depths[$child] - 1;

        $tree->lines[] = array(
          'start x' => $x,
          'end x' => $x,
          'start y' => $start_y,
          'end y' => $end_y,
        );
      } else {
        // one parent
        $x = $offsets[$parents[0]];
        $start_y = $depths[$parents[0]];
        $end_y = $depths[$child] - 1;

        $tree->lines[] = array(
          'start x' => $x,
          'end x' => $x,
          'start y' => $start_y,
          'end y' => $end_y,
        );
      }

      // a horizontal connecting line, if necessary
      if ($x != $offsets[$child]) {
        $start_x = $x;
        $end_x = $offsets[$child];
        $y = $depths[$child] - 1;

        $tree->lines[] = array(
          'start x' => $start_x,
          'end x' => $end_x,
          'start y' => $y,
          'end y' => $y,
        );
      }

      // a line from the connection depth to the child
      $x = $offsets[$child];
      $start_y = $depths[$child] - 1;
      $end_y = $depths[$child];

      $tree->lines[] = array(
        'start x' => $x,
        'end x' => $x,
        'start y' => $start_y,
        'end y' => $end_y,
      );
    }

    return $tree;
  }

  function getKeysThatWeWantToDisplay() {
    // for now just render all valid keys
    return array_keys($this->tree->getPeople());
  }

  function customParings() {
    return $this->custom_parings;
  }

  function getAllMarriageConnections($nodes_to_display = array()) {
    $connections = array();

    foreach ($this->tree->getPeople() as $person) {
      if (in_array($person->getKey(), $nodes_to_display)) {
        foreach ($person->married() as $m) {
          $spouse = $m["person"]->value()["person"]->getKey();
          if (in_array($spouse, $nodes_to_display)) {
            $connections[] = array(
              $person->getKey(),
              $m["person"]->value()["person"]->getKey(),
            );
          }
        }
      }
    }

    return $connections;
  }

  function getAllChildrenConnections($nodes_to_display) {
    $connections = array();

    foreach ($this->tree->getPeople() as $person) {
      if (!in_array($person->getKey(), $nodes_to_display)) {
        continue;
      }

      if ($person->parents()) {
        $parent_keys = array();
        foreach ($person->parents() as $parent) {
          if (isset($parent->value()['person'])) {
            $key = $parent->value()['person']->getKey();
            if (in_array($key, $nodes_to_display)) {
              $parent_keys[] = $key;
            }
          }
        }

        if ($parent_keys) {
          $connections[] = array(
            $person->getKey(),
            $parent_keys,
          );
        }
      }
    }

    return $connections;
  }

  function calculateDepths($children, $marriages, $nodes_to_display = array()) {
    $depths = array();

    // remove all references to nodes that are not in $nodes_to_display
    foreach ($children as $key => $child) {
      if (!in_array($child[0], $nodes_to_display)) {
        unset($children[$key]);
      }
    }
    foreach ($children as $key => $child) {
      foreach ($child[1] as $k => $p) {
        if (!in_array($p, $nodes_to_display)) {
          unset($children[$key][1][$k]);
        }
      }
    }

    // first put everyone at position zero
    foreach ($nodes_to_display as $node) {
      $depths[$node] = 0;
    }

    // do this ten times so we hopefully settle out
    for ($i = 0; $i < 10; $i++) {
      // now, the depth of a parent is the minimum of their current depth, and
      // (the depth of each child - 1)
      foreach ($children as $child) {
        $person = $child[0];
        foreach ($child[1] as $p) {
          $depths[$p] = min($depths[$p], $depths[$person] - 1);
        }
      }

      // put all people who are married on the highest depth
      foreach ($marriages as $m) {
        $min_depth = min($depths[$m[0]], $depths[$m[1]]);
        $depths[$m[0]] = $depths[$m[1]] = $min_depth;
      }

      // put all children at a depth at least 1 below the deepest
      // of their shared parents
      foreach ($children as $childs) {
        $child = $childs[0];
        $parents = $childs[1];
        $min_depth = 0;
        foreach ($parents as $p) {
          $min_depth = min($min_depth, $depths[$p]);
        }

        $depths[$child] = $min_depth + 1;
      }

    }

    // make all depths start at zero
    $min_depth = min($depths);
    foreach ($depths as $key => $depth) {
      $depths[$key] = ($depth - $min_depth) * 2;
    }

    return $depths;
  }

  function calculateOffsets($children, $marriages, $depths, $custom_parings = array()) {
    // split all nodes into layers
    $layers = array();
    foreach ($depths as $person => $depth) {
      if (!isset($layers[$depth])) {
        $layers[$depth] = array();
      }
      $layers[$depth][] = $person;
    }

    // sort the layer based on custom parings
    foreach ($custom_parings as $left => $right) {
      foreach ($layers as $i => $layer) {
        if (in_array($left, $layer) && in_array($right, $layer)) {
          $new_layer = array();
          foreach ($layer as $p) {
            if ($p == $left) {
              $new_layer[] = $left;
              $new_layer[] = $right;
            } elseif ($p == $right) {
              continue;
            } else {
              $new_layer[] = $p;
            }
          }
          $layers[$i] = $new_layer;
        }
      }
    }

    // assign an offset for each node, based on the order
    // in which they appear in the list, and also without
    // any sort of alignment
    $offsets = array();
    foreach ($layers as $layer) {
      $i = 0;
      foreach ($layer as $person) {
        $offsets[$person] = ($i++) * 2;
      }
    }

    // TODO more optimization will happen here hopefully one day

    return $offsets;
  }
}
