<?php
/**
 * Mpdf class to load Mpdf more quicly
 * Unsure we need it and we can really use it (need more use and extend …)
 * Vut allow using \Mpdf\ for other class …
 */
namespace limeMpdf\helper;
require_once __DIR__ . '/../vendor/autoload.php';


class Mpdf extends \Mpdf\Mpdf {

}
