<?php

/**
 * Make PDF WP Class 
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

class Make_PDF_WP {

  public $temp_folder;

  public function __construct() {
    // get wp-content folder
    $this->temp_folder = WP_CONTENT_DIR . "/temp/";
    // if folder does not exist, create it
    if (!file_exists($this->temp_folder)) {
      mkdir($this->temp_folder, 0775, true);
    }
  }

  /**
   * Get temp folder path
   * @return string
   */
  public function temp_path() {
    return $this->temp_folder;
  }

  /**
   * Default Options
   */
  public function options($array = []) {

    $options = [
      'mode' => !empty($array['mode']) ? $array['mode'] : "utf-8",
      'format' => !empty($array['format']) ? $array['format'] : [210, 297],
      'orientation' => !empty($array['orientation']) ? $array['orientation'] : "P",
      'margin_top' => !empty($array['margin_top']) ? $array['margin_top'] : 20,
      'margin_bottom' => !empty($array['margin_bottom']) ? $array['margin_bottom'] : 20,
      'margin_left' => !empty($array['margin_left']) ? $array['margin_left'] : 20,
      'margin_right' => !empty($array['margin_right']) ? $array['margin_right'] : 20,
      'margin_header' => !empty($array['margin_header']) ? $array['margin_header'] : 20,
      'margin_footer' => !empty($array['margin_footer']) ? $array['margin_footer'] : 20,
      'output' => !empty($array['output']) ? $array['output'] : "INLINE",
      'dest' => !empty($array['dest']) ? $array['dest'] : $this->temp_path(),
      'file_name' => !empty($array['file_name']) ? $array['file_name'] : time(),
      'header' => !empty($array['header']) ? $array['header'] : "",
      'footer' => !empty($array['footer']) ? $array['footer'] : "",
      'font' => !empty($array['font']) ? $array['font'] : "sans", // sans, cobdensed, serif, slab
      'debug' => !empty($array['debug']) && $array['debug'] == 1 ? true : false,
    ];

    //
    // Fonts
    //

    require_once(__DIR__ . "/mpdf/vendor/autoload.php");
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $options['fontDir'] = array_merge($fontDirs, [
      __DIR__ . '/fonts/',
    ]);

    $options['fontdata'] = $fontData + [ // lowercase letters only in font key
      'sans' => [
        'R' => 'roboto/Roboto-Regular.ttf',
        'I' => 'roboto/Roboto-Italic.ttf',
        'B' => 'roboto/Roboto-Bold.ttf',
        'L' => 'roboto/Roboto-Lite.ttf',
      ],
      'condensed' => [
        'R' => 'roboto-condensed/Roboto-Condensed-Regular.ttf',
        'I' => 'roboto-condensed/Roboto-Condensed-Italic.ttf',
        'B' => 'roboto-condensed/Roboto-Condensed-Bold.ttf',
        'L' => 'roboto-condensed/Roboto-Condensed-Lite.ttf',
      ],
      'serif' => [
        'R' => 'roboto-serif/Roboto-Serif-Regular.ttf',
        'I' => 'roboto-serif/Roboto-Serif-Italic.ttf',
        'B' => 'roboto-serif/Roboto-Serif-Bold.ttf',
        'L' => 'roboto-serif/Roboto-Serif-Lite.ttf',
      ],
      'slab' => [
        'R' => 'roboto-slab/Roboto-Slab-Regular.ttf',
        'B' => 'roboto-slab/Roboto-Slab-Bold.ttf',
        'L' => 'roboto-slab/Roboto-Slab-Lite.ttf',
      ],
      'mono' => [
        'R' => 'roboto-mono/Roboto-Mono-Regular.ttf',
        'I' => 'roboto-mono/Roboto-Mono-Italic.ttf',
        'B' => 'roboto-mono/Roboto-Mono-Bold.ttf',
        'L' => 'roboto-mono/Roboto-Mono-Lite.ttf',
      ],
    ];

    $options['default_font'] = $options['font'];

    // Add/override custom options
    if (count($array) > 0) {
      foreach ($array as $key => $val) $options[$key] = $val;
    }

    return $options;
  }


  /**
   * HTML to PDF
   * Simple converting html to pdf
   * @param string $html - html content to convert
   * @param array $options - array of options to set/override
   * @see $this->options() for params
   * @example $this->html2pdf($html, $options);
   */
  public function html2pdf($html, $params = []) {

    // pdf options
    $options = $this->options($params);

    // params
    $output     = $options['output'];
    $dest       = $options['dest'];
    $file_name  = $options['file_name'];
    $header     = $options['header'];
    $footer     = $options['footer'];

    $options['margin-header'] = 0;

    // mPDF
    require_once(__DIR__ . "/mpdf/vendor/autoload.php");

    //
    // Init
    //

    $mpdf = new \Mpdf\Mpdf($options);

    // Show image errors in debug mode
    if ($this->config->debug) {
      $mpdf->showImageErrors = true;
    }

    // Write html
    $stylesheet = file_get_contents(__DIR__ . '/css/mpdf.css');
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    if ($header != '') $mpdf->SetHTMLHeader($header);
    if ($footer != '') $mpdf->SetHTMLFooter($footer);
    $mpdf->WriteHTML($html);

    // Output
    if (!$output) {
      return $mpdf->Output();
    } elseif ($output == 'INLINE') { // display pdf in the browser - default
      return $mpdf->Output($file_name . ".pdf", \Mpdf\Output\Destination::INLINE);
    } elseif ($output == 'DOWNLOAD') { // trigger download pdf
      return $mpdf->Output($file_name . ".pdf", \Mpdf\Output\Destination::DOWNLOAD);
    } elseif ($output == "FILE") { // download file in specified path
      return $mpdf->Output("{$dest}{$file_name}.pdf", \Mpdf\Output\Destination::FILE);
    }
  }
}
