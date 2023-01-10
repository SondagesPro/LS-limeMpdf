<?php

/**
 * Plugin helper for limesurvey : add mpdf as an helper and some other tool for using it
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019-2023 Denis Chenu <http://www.sondages.pro>
 * @license AGPL v3
 * @version 2.2.1
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
    protected $storage = 'DbStorage';

    protected static $description = 'Add mpdf as a plugin helper and some other tool for using it.';
    protected static$name = 'limeMpdf';

    public function init()
    {
        if (version_compare(Yii::app()->getConfig('versionnumber'), "3.10.0", "<=")) {
            $this->subscribe('beforeActivate');
            return;
        }
        Yii::setPathOfAlias('limeMpdf', dirname(__FILE__));
        /* For the demao */
        $this->subscribe('newDirectRequest');
        /* for twig registering */
        $this->subscribe('limeMpdfBeforePdf');
        /* for twig edit */
        if (intval(App()->getConfig('versionnumber')) <= 3) {
            /* see mantis issue https://bugs.limesurvey.org/view.php?id=18589 Unable to use getValidScreenFiles event */
            $this->subscribe('getValidScreenFiles');
        }
    }

    public static function getDescription()
    {
        if (version_compare(Yii::app()->getConfig('versionnumber'), "3.10.0", "<=")) {
            return 'This plugin is not compatible with your version. this plugin need LimeSurvey 3.10.0 or up.';
        }
        return parent::getDescription();
    }

    /**
     * Disable activate in 3.9 and lesser
     */
    public function beforeActivate()
    {
        if (!$this->getEvent()) {
            throw new CHttpException(403);
        }

        // No need to test (registered only for 3.9 and lesser) but more clear
        if (version_compare(Yii::app()->getConfig('versionnumber'), "3.10.0", "<=")) {
            $event = $this->getEvent();
            $event->set('success', false);
            // Optionally set a custom error message.
            $event->set('message', 'This plugin is not compatible with your version. this plugin need LimeSurvey 3.10.0 or up.');
        }
    }

    /**
     * Register to own event to register to getPluginTwigPath event
     */
    public function limeMpdfBeforePdf()
    {
        if (!$this->getEvent()) {
            throw new CHttpException(403);
        }
        $this->subscribe('getPluginTwigPath');
    }

    /**
     * Add some views for this and other plugin
     */
    public function getPluginTwigPath()
    {
        if (!$this->getEvent()) {
            throw new CHttpException(403);
        }

        $viewPath = dirname(__FILE__)."/views";
        $this->getEvent()->append('add', array($viewPath));
    }

    public function getValidScreenFiles()
    {
        if (!$this->getEvent()) {
            throw new CHttpException(403);
        }
        if(
            $this->getEvent()->get("type")!='view' ||
            ($this->getEvent()->get("screen") && $this->getEvent()->get("screen")!="pdf")
        ){
            return;
        }
        $this->subscribe('getPluginTwigPath');
        $addTwigs = array(
            "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."header.twig",
            "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."body.twig",
            "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."footer.twig",
            "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."style-custom.twig",
        );
        if ($this->get('twigEditDemo',null,null,1) && App()->getRequest()->getQuery('templatename') == App()->getConfig('defaulttheme')) {
            $addTwigs[] = "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."demo-html.twig";
        }
        if ($this->get('twigEditButtonTag',null,null,0)) {
            $addTwigs[] = "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."radio.twig";
            $addTwigs[] = "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."radio-checked.twig";
            $addTwigs[] = "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."checkbox.twig";
            $addTwigs[] = "subviews".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."checkbox-checked.twig";
        }
        $this->getEvent()->append('add', $addTwigs);
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
        'twigEditDemo' => array(
            'type' => 'boolean',
            'default' => 1,
            'label' => 'Allow edit of demo in default theme',
        ),
        'twigEditButtonTag' => array(
            'type' => 'boolean',
            'default' => 0,
            'label' => 'Allow edit of button tags',
        ),
    );

    /**
     * @see parent:getPluginSettings
     */
    public function getPluginSettings($getValues=true)
    {
        if(!Permission::model()->hasGlobalPermission('settings','read')) {
            throw new CHttpException(403);
        }
        $pluginSettings= parent::getPluginSettings($getValues);
        if (version_compare(Yii::app()->getConfig('versionnumber'),"3.10.0","<=")) {
            $pluginSettings['linkDemoView']['content'] = "<div class='alert alert-danger error'>This plugin is not compatible with your version. this plugin need LimeSurvey 3.10.0 or up.</div>";
            $pluginSettings['linkDemoDownload']['content'] = "";
            return;
        }
        $testLink = Yii::app()->getController()->createUrl('plugins/direct', array('plugin' => get_class($this),'function'=>'view'));
        $testLink = CHtml::link($testLink,$testLink);
        $pluginSettings['linkDemoView']['content'] = sprintf($pluginSettings['linkDemoView']['content'],$testLink);
        $testLink = Yii::app()->getController()->createUrl('plugins/direct', array('plugin' => get_class($this),'function'=>'download'));
        $testLink = CHtml::link($testLink,$testLink);
        $pluginSettings['linkDemoDownload']['content'] = sprintf($pluginSettings['linkDemoDownload']['content'],$testLink);
        return $pluginSettings;
    }

    public function newDirectRequest()
    {
        if (!$this->getEvent()) {
            throw new CHttpException(403);
        }
        if($this->getEvent()->get('target') != get_class($this)) {
            return;
        }
        if(!Permission::getUserId()) {
            /* Making PDF take memory, disable if not loggued */
            throw new CHttpException(401);
        }

        App()->setLanguage(Yii::app()->session['adminlang']);
        $function = $this->getEvent()->get('function');
        /* event updated here */
        $this->subscribe('getPluginTwigPath');
        $html = Yii::app()->twigRenderer->renderPartial('./subviews/mpdf/demo-html.twig', array('aSurveyInfo'=>array()));
        $pdfHelper = new \limeMpdf\helper\limeMpdfHelper();

        $pdfHelper->setOptions(array(
            'h2bookmarks' => array('H1'=>0, 'H2'=>1, 'H3'=>2),
            'h2toc' => array('H1'=>0, 'H2'=>1, 'H3'=>2),
            //~ 'title2annots' => true,
        ));
        $pdfHelper->setTitle("Demo of limeMpdf",Yii::app()->getConfig('sitename'));
        $pdfHelper->filterHtml = false;
        if($function == 'view') {
            $pdfHelper->doPdfContent($html,\Mpdf\Output\Destination::INLINE);
        }
        $pdfHelper->doPdfContent($html);
    }
}
