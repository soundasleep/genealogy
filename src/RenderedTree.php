<?php

namespace Genealogy;

class RenderedTree {
  // array of (x, y, text =>)
  var $nodes = array();

  // array of (start x, start y, end x, end y, text =>)
  var $lines = array();

  // array of (start x, end x, y, text =>)
  var $double_lines = array();

  // scale of x
  var $x_scale = 100;

  // scale of y
  var $y_scale = 50;

  function __construct() {
    // generate a test tree
    $this->nodes[] = array('x' => 3, 'y' => 0, 'text' => 'A', 'class' => 'red');
    $this->nodes[] = array('x' => 5, 'y' => 0, 'text' => 'B');
    $this->nodes[] = array('x' => 0, 'y' => 2, 'text' => 'C');
    $this->nodes[] = array('x' => 2, 'y' => 2, 'text' => 'D');
    $this->nodes[] = array('x' => 4, 'y' => 2, 'text' => 'E');
    $this->nodes[] = array('x' => 6, 'y' => 2, 'text' => 'F');

    $this->lines[] = array(
      'start x' => 4, 'start y' => 0,
      'end x' => 4, 'end y' => 1,
    );
    $this->lines[] = array(
      'start x' => 2, 'start y' => 1,
      'end x' => 6, 'end y' => 1,
    );
    $this->lines[] = array(
      'start x' => 2, 'start y' => 1,
      'end x' => 2, 'end y' => 2,
    );
    $this->lines[] = array(
      'start x' => 4, 'start y' => 1,
      'end x' => 4, 'end y' => 2,
    );
    $this->lines[] = array(
      'start x' => 6, 'start y' => 1,
      'end x' => 6, 'end y' => 2,
    );

    $this->double_lines[] = array('start x' => 3, 'end x' => 5, 'y' => 0);
    $this->double_lines[] = array('start x' => 0, 'end x' => 2, 'y' => 2);
  }

  function render() {
    $output = array();

    $output = $this->renderLines($output);
    $output = $this->renderDoubleLines($output);
    $output = $this->renderNodes($output);

    return implode("\n", $output);
  }

  function lineStyle($line) {
    if (isset($line['y'])) {
      $line += array(
        'start y' => $line['y'],
        'end y' => $line['y'],
      );
    }

    $left = (($line['start x'] + 0.5) * $this->x_scale);
    $top = (($line['start y'] + 0.5) * $this->y_scale);
    $calculated_width = ($line['end x'] - $line['start x']);
    $calculated_height = ($line['end y'] - $line['start y']);
    $width = $this->x_scale * $calculated_width;
    $height = $this->y_scale * $calculated_height;

    return htmlspecialchars("top: ${top}px; left: ${left}px; width: ${width}px; height: ${height}px;");
  }

  function renderLines($output = array()) {
    foreach ($this->lines as $line) {
      $line += array(
        'data' => false,
        'class' => "",
      );

      $style = htmlspecialchars($this->lineStyle($line));
      $output[] = "<div class=\"line " . htmlspecialchars($line['class']) . "\" style=\"$style\"></div>";
    }

    return $output;
  }

  function renderDoubleLines($output = array()) {
    foreach ($this->double_lines as $line) {
      $line += array(
        'data' => false,
        'class' => "",
      );

      $style = htmlspecialchars($this->lineStyle($line));
      $output[] = "<div class=\"double-line " . htmlspecialchars($line['class']) . "\" style=\"$style\"></div>";
    }

    return $output;
  }

  function renderNodes($output = array()) {
    foreach ($this->nodes as $node) {
      $node += array(
        'data' => false,
        'class' => "",
      );

      $left = ($node['x'] * $this->x_scale);
      $top = ($node['y'] * $this->y_scale);
      $width = $this->x_scale;
      $height = $this->y_scale;
      $style = htmlspecialchars("top: ${top}px; left: ${left}px; width: ${width}px; height: ${height}px;");

      $output[] = "<div class=\"node " . htmlspecialchars($node['class']) . "\" style=\"$style\">" . $node['text'] . "</div>";
    }

    return $output;
  }
}
