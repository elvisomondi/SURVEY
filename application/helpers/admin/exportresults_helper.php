<?php

Yii::import('application.helpers.admin.export.*');
class ExportSurveyResultsService
{
    /**
     * Hold the available export types
     * 
     * @var array
     */
    protected $_exports;
    
    
    function exportSurvey($iSurveyId, $sLanguageCode, $sExportPlugin, FormattingOptions $oOptions, $sFilter = '')
    {
        //Do some input validation.
        if (empty($iSurveyId))
        {
            safeDie('A survey ID must be supplied.');
        }
        if (empty($sLanguageCode))
        {
            safeDie('A language code must be supplied.');
        }
        if (empty($oOptions))
        {
            safeDie('Formatting options must be supplied.');
        }
        if (empty($oOptions->selectedColumns))
        {
            safeDie('At least one column must be selected for export.');
        }
        //echo $oOptions->toString().PHP_EOL;
        $writer = null;

        $iSurveyId = sanitize_int($iSurveyId);
        if ($oOptions->output=='display')
        {
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
        }
        
        $exports = $this->getExports();
        
        if (array_key_exists($sExportPlugin, $exports) && !empty($exports[$sExportPlugin])) {
            // This must be a plugin, now use plugin to load the right class
            $event = new PluginEvent('newExport');
            $event->set('type', $sExportPlugin);
            $oPluginManager = App()->getPluginManager();
            $oPluginManager->dispatchEvent($event, $exports[$sExportPlugin]);
            $writer = $event->get('writer');
        }
        
        if (!($writer instanceof IWriter)) {
            throw new Exception(sprintf('Writer for %s should implement IWriter', $sExportPlugin));
        }

        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($iSurveyId, $sLanguageCode);
        $writer->init($survey, $sLanguageCode, $oOptions);
                
        $surveyDao->loadSurveyResults($survey, $oOptions->responseMinRecord, $oOptions->responseMaxRecord, $sFilter, $oOptions->responseCompletionState);
        
        $writer->write($survey, $sLanguageCode, $oOptions,true);
        $result = $writer->close();
        
        // Close resultset if needed
        if ($survey->responses instanceof CDbDataReader) {
            $survey->responses->close();
        }
        
        if ($oOptions->output=='file')
        {
            return $writer->filename;
        } else {
            return $result;
        }
    }
    
    /**
     * Get an array of available export types
     * 
     * @return array
     */
    public function getExports()
    {
        if (is_null($this->_exports)) {
            $event = new PluginEvent('listExportPlugins');
            $oPluginManager = App()->getPluginManager();
            $oPluginManager->dispatchEvent($event);

            $exports = $event->get('exportplugins', array());
            
            $this->_exports = $exports;
        }
        
        return $this->_exports;
    }
}
