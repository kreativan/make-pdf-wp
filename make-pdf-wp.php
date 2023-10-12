<?php

/**
 *  Plugin Name: MakePDF WP
 *  Description: Generate PDF using mpdf library
 *  Version: 0.0.1
 *  Author: Ivan Milincic
 *  Author URI: http://kreativan.dev/
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

function make_pdf_wp() {
  require __DIR__ . '/class.php';
  return new Make_PDF_WP();
}
