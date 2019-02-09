<?php
/**
 * limeMpdfHelper part of renderMessage Plugin
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2017 Denis Chenu <http://www.sondages.pro>
 * @license AGPL v3
 * @version 0.1.0-dev
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
namespace limeMpdf\helper;
use limeMpdf\Mpdf;
use Yii;
use CHtmlPurifier;
use Template;
use Survey;

class limeMpdfHelper {

    /* @var string */
    public $title = "";
    /* @var string */
    public $subtitle = "";
    /* @var string|null */
    public $logo = "";
    /* @var integer|null */
    public $surveyId = null;

    /**
     * @param integer $surveyId
     */
    public function _contruct($surveyId = null)
    {
        $this->title = Yii::app()->getConfig('sitename');
        if($surveyId) {
            if(empty(Survey::model()->findByPk($surveyId))) {
                return;
            }
            $this->surveyId = $surveyId;
            $this->title = Survey::model()->findByPk($options['surveyid'])->getLocalizedTitle();
            $this->subtitle = Yii::app()->getConfig('sitename');
        }
    }

    /**
     * set the title and subtitle
     * @param string $title
     * @param string|null $subtitle
     * @return void
     */
    public function setTitle($title,$subtitle=null)
    {
        $this->title = $title;
        if(is_string($subtitle)) {
            $this->subtitle = $subtitle;
        }
    }

    /**
     * Do the pdf content and send it to borwser for download
     * @param $html string
     * @param $output string \limeMpdf\Mpdf\Output\Destination
     * @return void
     */
    public function doPdfContent($html) // ,\limeMpdf\Mpdf\Output\Destination::DOWNLOAD)
    {
        $mpdfOptions = array(
            //~ 'h2bookmarks' => array('H1'=>0, 'H2'=>1, 'H3'=>2),
            //~ 'h2toc' => array('H1'=>0, 'H2'=>1, 'H3'=>2),
        );
        if($this->title) {
            $mpdfOptions['setAutoTopMargin'] = 'pad';
        }

        $mpdf = new \limeMpdf\helper\Mpdf($mpdfOptions);
        $html = $this->cleanUpAndFixHtml($html);
        $renderData = array(
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'aSurveyInfo' => array(),
        );
        if($this->surveyId) {
            $renderData['aSurveyInfo'] = getSurveyInfo($this->surveyId, App()->getLanguage());
        }
        $headerHtml = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/header.twig', $renderData);
        if(trim($headerHtml)) {
            $mpdf->SetHTMLHeader($headerHtml);
        }
        $renderData['content'] = $html;
        $bodyHtml = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/body.twig', $renderData);
        $stylesheet = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/stylesheet.twig', $renderData);
        $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($bodyHtml,\Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output();
    }


    /**
     * Clean up an html string
     * @parm string $html
     * @return string
     */
    public function cleanUpAndFixHtml($html)
    {
        $html = str_replace(array('<pagebreak>','<pagebreak />'),array('<br class="pagebreak" />','<br class="pagebreak" />'),$html);
        $oPurifier = new CHtmlPurifier();
        $oPurifier->options = array(
            'AutoFormat.RemoveEmpty'=>false,
            'Core.NormalizeNewlines'=>false,
            'CSS.AllowTricky'=>true, // Allow display:none; (and other)
            'CSS.Trusted' => true,
            'Attr.EnableID'=>true, // Allow to set id
            'Attr.AllowedFrameTargets'=>array('_blank','_self'),
            'URI.AllowedSchemes'=>array(
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                'data' => true,
                )
        );
        $html=$oPurifier->purify($html);
        $html = str_replace('<br class="pagebreak" />','<pagebreak />',$html);
        return $html;
    }
}
