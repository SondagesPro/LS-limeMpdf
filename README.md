# limeMpdf

Allow plugins to use [mPdf](https://mpdf.github.io/) to generate PDF document.

Plugin tested on LimeSurvey 3 and 5. Untested on LimeSurvey 6.

## Installation

### Via GIT
- Go to your LimeSurvey Directory
- Clone in plugins/pdfReport directory `git clone https://gitlab.com/SondagesPro/coreAndTools/limeMpdf.git limeMpdf`

### Via ZIP dowload
- Download <https://dl.sondages.pro/limeMpdf.zip>
- Extract : `unzip pdfReport.zip`
- Move the directory to  plugins/ directory inside LimeSurvey

## Simple usage

A complete documentation using mPdf can be view at Plugin Settings. You can see this documentation at [this repository](assets/Demo%20of%20limeMpdf.pdf).

### Basic usage

````
$html = "<p>Some HTML to put in your PDF.</p>";
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

If you want to update this file globally : create a new theme based on your desired default theme. In this extended theme directory (`upload/survey/yourtheme`), create `views/subviews/mpdf` diretory.
Create or copy the file you want to replace in this directory.

Each twig files get this variables : `title`,`subtiles` and `aSurveyInfo`.

- `body.twig` : the final content of you PDF, the content are inside `content` variable.
- `head.twig` : the final header set, it‘s empty : no header was set.
- `footer.twig` : the final footer set.
- `stylesheet.twig` : the stylesheet used, by default include
    - style-bootstrap.twig : bootstrap inspired part
    - style-helper.twig : some specific helper (input-text, inner-col ...)
    - style-custom.twig : an empty file. It stay empty in this plugin, best place to add your own style.

#### Adding new specific tag

New tags file specific for limeMpdf must be in `views/subviews/mpdf/tags` and muts named as tagname.twig. You arlerady have radio and checknow tag for insipration.

### limeMpdfHelper option and function

- `$limeMpdfHelper->setTitle($title,$subtitle=null);` : set the title and the sub title for header.
- `$limeMpdfHelper->setOptions($options = array());` : set specific option for Mpdf. This don‘t reset whole options, just set this in array.

### Compatibility

The plugin can work on all 3.X, 2.73, 4.X and 5.X version (without warranty).

- 1.0.0 version was tested on 3.22.25.
- 2.2.0 versin was tested on 5.1.13 

### Api update

- 2.1 : theme : move svg element to tag directly
- 2 : theme : rename svg file : `filename.svg.twig` to `filename.twig`
- 1 : 1st stable release

## Home page & Copyright
- HomePage <http://extensions.sondages.pro/>
- Copyright © 2019-2023 Denis Chenu <https://sondages.pro>
- Include mPDF [Copyright © 2010 Ian Back](https://mpdf.github.io/about-mpdf/license.html) under GPL licence ([Credit](https://mpdf.github.io/about-mpdf/credits.html))
- Licence : GNU Affero General Public License <https://www.gnu.org/licenses/agpl-3.0.html>
- [Donate](https://support.sondages.pro/open.php?topicId=12), [Liberapay](https://liberapay.com/SondagesPro/), [OpenCollective](https://opencollective.com/sondagespro) 

## Support
- Issues <https://gitlab.com/SondagesPro/mailing/sendMailCron/issues>
- Professional support <https://support.sondages.pro/>
