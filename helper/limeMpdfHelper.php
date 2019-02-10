<?php
/**
 * limeMpdfHelper part of renderMessage Plugin
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2017 Denis Chenu <http://www.sondages.pro>
 * @license AGPL v3
 * @version 0.1.1-dev
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
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf;
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
    /* @var array() @see https://mpdf.github.io/configuration/configuration-v7-x.html#constructor-configuration */
    public $mpdfOptions = array();
    /* @var string|null */
    public $filename = null;
    /* @var boolean */
    public $filterHtml = true;
    /* @var boolean */
    public $helperTags = true;
    /* @var string[] */
    public $aKnowTags = array(
        'radio',
        'radio-checked',
        'checkbox',
        'checkbox-checked',
    );
    /**
     * @param integer $surveyId
     */
    public function __construct($surveyId = null)
    {
        $this->title = Yii::app()->getConfig('sitename');
        $this->mpdfOptions = array(
            'mode' => 'c', // To review : utf-8 use dejavusans, c only default PDF font
            'setAutoTopMargin' =>'pad',
            'tempDir' => Yii::app()->getRuntimePath(), /* @todo : create own dir and cleaning it */
        );
        if($surveyId) {
            if(empty(Survey::model()->findByPk($surveyId))) {
                return;
            }
            $this->surveyId = $surveyId;
            $this->setTitle(
                Survey::model()->findByPk($surveyId)->getLocalizedTitle(),
                Yii::app()->getConfig('sitename')
            );
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
        if(empty($title)) {
            $this->title = null;
            $this->mpdfOptions['setAutoTopMargin'] = false;
            return;
        }
        $this->title = $title;
        if(is_string($subtitle)) {
            $this->subtitle = $subtitle;
        }
    }

    /**
     * set the options for Mpdf
     * @param array $options @see https://mpdf.github.io/configuration/configuration-v7-x.html
     * @return void
     */
    public function setOptions($options = array())
    {
        $this->mpdfOptions = array_merge($this->mpdfOptions,$options);
    }

    /**
     * Do the pdf content and send it to borwser for download
     * @param $html string
     * @param $output string \limeMpdf\Mpdf\Output\Destination
     * @return void
     */
    public function doPdfContent($html,$output = \Mpdf\Output\Destination::DOWNLOAD )
    {
        /* Set template */
        \Template::resetInstance();
        $oTemplate = \Template::model()->getInstance(null, $this->surveyId);

        if($this->helperTags) {
            $html = $this->replaceSpecificTags($html);
        }
        if($this->filterHtml) {
            $html = $this->cleanUpHtml($html);
        }
        $renderData = array(
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'aSurveyInfo' => array(),
        );

        if(is_null($this->filename)) {
            $this->filename = sanitize_filename($this->title,false,false,false);
        }

        if($this->surveyId) {
            $renderData['aSurveyInfo'] = getSurveyInfo($this->surveyId, App()->getLanguage());
        }
        $headerHtml = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/header.twig', $renderData);
        $renderData['content'] = $html;
        $bodyHtml = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/body.twig', $renderData);
        $stylesheet = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/stylesheet.twig', $renderData);
        try {
            $mpdf = new \Mpdf\Mpdf($this->mpdfOptions);
            if(trim($headerHtml)) {
                $mpdf->SetHTMLHeader($headerHtml);
            }
            $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($bodyHtml,\Mpdf\HTMLParserMode::HTML_BODY);
            $mpdf->Output($this->filename.".pdf",$output);
        } catch (\Mpdf\MpdfException $e) {
            /* @todo : review with debug=0 */
            throw new CHttpException(500,$e->getMessage());
        }
    }


    /**
     * Clean up an html string
     * @parm string $html
     * @return string
     */
    public function cleanUpHtml($html)
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

    /**
     * Specific tags replacement
     * @param string $html
     * @return string
     */
    public function replaceSpecificTags($html)
    {
        $renderData = array(
            'aSurveyInfo' => array(),
            'defaultBasePath' => 'plugins/limeMpdf/assets/'
        );
        if($this->surveyId) {
            $renderData['aSurveyInfo'] = getSurveyInfo($this->surveyId, App()->getLanguage());
        }
        foreach($this->aKnowTags as $tag) {
            $replacement = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/'.$tag.'.twig', $renderData);
            $html = str_replace("<".$tag.">",$replacement,$html);
        }
        return $html;
    }
}
