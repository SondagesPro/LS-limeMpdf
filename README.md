# limeMpdf

Allow plugins to use [mPdf](https://mpdf.github.io/) to generate PDF document.

## Installation

### Via GIT
- Go to your LimeSurvey Directory
- Clone in plugins/pdfReport directory `git https://gitlab.com/SondagesPro/coreAndTools/limeMpdf.git limeMpdf`

### Via ZIP dowload
- Download <https://dl.sondages.pro/limeMpdf.zip>
- Extract : `unzip pdfReport.zip`
- Move the directory to  plugins/ directory inside LimeSurvey

## Simple usage

A documentation using mPdf can be view at Plugin Settings.

### Basic usage

````
$html = "&lt;p&gt;Some HTML to put in your PDF.&lt;/p&gt;";
$limeMpdfHelper = new \limeMpdf\helper\limeMpdfHelper();
/* Send pdf file to browser for download */
$limeMpdfHelper->doPdfContent($html);
````

In constructor you can set the surveyid to be used for Template used in generator.
````
$limeMpdfHelper = new \limeMpdf\helper\limeMpdfHelper($surveyId);
````

### Adapt html and css
The twig files used are in `./views/subviews/mpdf/`. Then if this files are in template used : it replace the default twig file from plugin.

Each twig files get this variables : `title`,`subtiles` and `aSurveyInfo`.

- `body.twig` : the final content of you PDF, the content are inside `content` variable.
- `head.twig` : the final header set, it‘s empty : no header was set.
- `stylesheet.twig` : the stylesheet used, by default include style-bootstrap.twig, style-helper.twig and style-custom.twig. style-custom.twig are empty and stay empty in this plugin.</li>

### limeMpdfHelper option and function

- `$limeMpdfHelper->setTitle($title,$subtitle=null);` : set the title and the sub title for header.
- `$limeMpdfHelper->setOptions($options = array());` : set specific option for Mpdf. This don‘t reset whole options, just set this in array.

## Home page & Copyright
- HomePage <http://extensions.sondages.pro/>
- Copyright © 2019 Denis Chenu <https://sondages.pro>
- Include mPDF [Copyright © 2010 Ian Back](https://mpdf.github.io/about-mpdf/license.html) under GPL licence ([Credit](https://mpdf.github.io/about-mpdf/credits.html))
- [![Donate](https://liberapay.com/assets/widgets/donate.svg)](https://liberapay.com/SondagesPro/) : [Donate on Liberapay](https://liberapay.com/SondagesPro/)

Distributed under [GNU AFFERO GENERAL PUBLIC LICENSE Version 3](http://www.gnu.org/licenses/agpl.txt) licence
