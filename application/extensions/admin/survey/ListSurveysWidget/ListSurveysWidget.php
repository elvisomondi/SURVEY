<?php
/**
 * This widget display the list of surveys.
 */
class ListSurveysWidget extends CWidget
{
    public $model;                                                              // Survey model
    public $bRenderFooter    = true;                                            // Should the footer be rendered?
    public $bRenderSearchBox = true;                                            // Should the search box be rendered?
    public $formUrl          = 'admin/survey/sa/listsurveys/';

    public $massiveAction;                                                      // Used to render massive action in GridViews footer
    public $pageSize;                                                           // Default page size (should be set to Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']))
    public $template;

    public function run()
    {

        // Search
        if (isset($_GET['Survey']['searched_value']))
        {
            $this->model->searched_value = $_GET['Survey']['searched_value'];
        }

        $this->model->active = null;

        // Filter state
        if (isset($_GET['active']) && !empty($_GET['active']))
        {
            $this->model->active = $_GET['active'];
        }



        // Set number of page
        if (isset($_GET['pageSize']))
        {
            Yii::app()->user->setState('pageSize',(int)$_GET['pageSize']);
        }

        $this->pageSize = Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

        Yii::app()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/reload.js'));

        $this->massiveAction = $this->render('massive_actions/_selector', array(), true, false);

        if ($this->bRenderFooter)
        {
            $this->template = "{items}\n<div class=\"row-fluid\"><div class=\"col-sm-4\" id=\"massive-action-container\">$this->massiveAction</div><div class=\"col-sm-4 pager-container \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>";
        }
        else
        {
            $this->template = "{items}";
        }

        if ($this->bRenderSearchBox)
        {
            $this->render('searchBox');
        }

        $this->render('listSurveys');

    }
}
