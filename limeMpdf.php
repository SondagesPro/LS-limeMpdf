<?php
/**
 * Plugin helper for limesurvey : add mpdf as an helper and some other tool for using it
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019 Denis Chenu <http://www.sondages.pro>
 * @license AGPL v3
 * @version 0.0.1-dev
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
class limeMpdf extends PluginBase {

    static protected $description = 'Add mpdf as a plugin helper and some other tool for using it.';
    static protected $name = 'limeMpdf';

    public function init()
    {
        Yii::setPathOfAlias('limeMpdf', dirname(__FILE__));
        $this->subscribe('getPluginTwigPath');
        $this->subscribe('newDirectRequest');
    }
    /**
     * Add some views for this and other plugin
     */
    public function getPluginTwigPath()
    {
        $viewPath = dirname(__FILE__)."/views";
        $this->getEvent()->append('add', array($viewPath));
    }

    /**
    * @var array[] the settings
    */
    protected $settings = array(
        'linkDemoView' => array(
            'type' => 'info',
            'content' => 'See the generated demonstration using default template %s.',
        ),
        'linkDemoDownload' => array(
            'type' => 'info',
            'content' => 'Download the generated demonstration using default template %s.',
        ),
    );

    /**
     * @see parent:getPluginSettings
     */
    public function getPluginSettings($getValues=true)
    {
        $pluginSettings= parent::getPluginSettings($getValues);
        $testLink = $this->api->createUrl('plugins/direct', array('plugin' => get_class($this),'function'=>'view'));
        $testLink = CHtml::link($testLink,$testLink);
        $pluginSettings['linkDemoView']['content'] = sprintf($pluginSettings['linkDemoView']['content'],$testLink);
        $testLink = $this->api->createUrl('plugins/direct', array('plugin' => get_class($this),'function'=>'download'));
        $testLink = CHtml::link($testLink,$testLink);
        $pluginSettings['linkDemoDownload']['content'] = sprintf($pluginSettings['linkDemoDownload']['content'],$testLink);
        return $pluginSettings;
    }

    public function newDirectRequest()
    {
        if($this->getEvent()->get('target') != get_class($this)) {
            return;
        }
        $html = Yii::app()->twigRenderer->renderPartial('./subviews/mpdfHelper/demo-html.twig', array('aSurveyInfo'=>array()));

        $pdfHelper = new \limeMpdf\helper\limeMpdfHelper();
        $pdfHelper->setTitle("Demo of limeMpdf",Yii::app()->getConfig('sitename'));
        $pdfHelper->doPdfContent($html);
    }
}
