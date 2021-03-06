<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class responses extends Survey_Common_Action
{

    /**
    * @var string : Default layout is bare : temporary to real layout
    */
    public $layout = 'bare';

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('surveytranslator');
    }

    
    private function _getData($params)
    {
        if (is_numeric($params))
        {
            $iSurveyId = $params;
        }
        elseif (is_array($params))
        {
            extract($params);
        }
        $aData = array();
        // Set the variables in an array
        $aData['surveyid'] = $aData['iSurveyId'] = (int) $iSurveyId;
        if (!empty($iId))
        {
            $aData['iId'] = (int) $iId;
        }
        $aData['imageurl'] = Yii::app()->getConfig('imageurl');
        $aData['action'] = Yii::app()->request->getParam('action');
        $aData['all']=Yii::app()->request->getParam('all');
        $thissurvey=getSurveyInfo($iSurveyId);
        if(!$thissurvey)// Already done in Survey_Common_Action
        {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            $this->getController()->redirect(array("admin/index"));
        }
        elseif($thissurvey['active'] != 'Y')
        {
            Yii::app()->session['flashmessage'] = gT("This survey has not been activated. There are no results to browse.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

        $aData['surveyinfo'] = $thissurvey;

        if (Yii::app()->request->getParam('browselang'))
        {
            $aData['language'] = Yii::app()->request->getParam('browselang');
            $aData['languagelist'] = $languagelist = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            $aData['languagelist'][] = Survey::model()->findByPk($iSurveyId)->language;
            if (!in_array($aData['language'], $languagelist))
            {
                $aData['language'] = $thissurvey['language'];
            }
        }
        else
        {
            $aData['language'] = $thissurvey['language'];
        }

        $aData['qulanguage'] = Survey::model()->findByPk($iSurveyId)->language;

        $aData['surveyoptions'] = '';
        $aData['browseoutput']  = '';

        return $aData;
    }

    public function getActionParams()
    {
        return array_merge($_GET,$_POST);
    }

    public function viewbytoken($iSurveyID, $token, $sBrowseLang = '')
    {
        // Get Response ID from token
        $oResponse = SurveyDynamic::model($iSurveyID)->findByAttributes(array('token'=>$token));
        if (!$oResponse){
            Yii::app()->setFlashMessage(gT("Sorry, this response was not found."),'error');
            $this->getController()->redirect(array("admin/responses/sa/browse/surveyid/{$iSurveyID}"));
        }
        else
        {
            $this->getController()->redirect(array("admin/responses/sa/view/surveyid/{$iSurveyID}/id/{$oResponse->id}"));
        }

    }


    /**
    * View a single response in detail
    *
    * @param mixed $iSurveyID
    * @param mixed $iId
    * @param mixed $sBrowseLang
    */
    public function view($iSurveyID, $iId, $sBrowseLang = '')
    {
        if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','read'))
        {
            $aData = $this->_getData(array('iId' => $iId, 'iSurveyId' => $iSurveyID, 'browselang' => $sBrowseLang));
            $sBrowseLanguage = $aData['language'];

            extract($aData);

            $aViewUrls = array();

            $fieldmap = createFieldMap($iSurveyID, 'full', false, false, $aData['language']);
            $bHaveToken=$aData['surveyinfo']['anonymized'] == "N" && tableExists('tokens_' . $iSurveyID);// Boolean : show (or not) the token
            if(!Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read')) // If not allowed to read: remove it
            {
                unset($fieldmap['token']);
                $bHaveToken=false;
            }
            //add token to top of list if survey is not private
            if ($bHaveToken)
            {
                $fnames[] = array("token", gT("Token ID"), 'code'=>'token');
                $fnames[] = array("firstname", gT("First name"), 'code'=>'firstname');// or token:firstname ?
                $fnames[] = array("lastname", gT("Last name"), 'code'=>'lastname');
                $fnames[] = array("email", gT("Email"), 'code'=>'email');
            }
            $fnames[] = array("submitdate", gT("Submission date"), gT("Completed"), "0", 'D','code'=>'submitdate');
            $fnames[] = array("completed", gT("Completed"), "0");

            foreach ($fieldmap as $field)
            {
                if ($field['fieldname'] == 'lastpage' || $field['fieldname'] == 'submitdate')
                    continue;
                if ($field['type'] == 'interview_time')
                    continue;
                if ($field['type'] == 'page_time')
                    continue;
                if ($field['type'] == 'answer_time')
                    continue;

                //$question = $field['question'];
                $question = viewHelper::getFieldText($field);

                if ($field['type'] != "|")
                {
                    $fnames[] = array($field['fieldname'], viewHelper::getFieldText($field),'code'=>viewHelper::getFieldCode($field,array('LEMcompat'=>true)));
                }
                elseif ($field['aid'] !== 'filecount')
                {
                    $qidattributes = getQuestionAttributeValues($field['qid']);

                    for ($i = 0; $i < $qidattributes['max_num_of_files']; $i++)
                    {
                        $filenum=sprintf(gT("File %s"),$i + 1);
                        if ($qidattributes['show_title'] == 1)
                            $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('Title').")",'code'=>viewHelper::getFieldCode($field).'(title)', "type" => "|", "metadata" => "title", "index" => $i);

                        if ($qidattributes['show_comment'] == 1)
                            $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('Comment').")",'code'=>viewHelper::getFieldCode($field).'(comment)', "type" => "|", "metadata" => "comment", "index" => $i);

                        $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('File name').")",'code'=>viewHelper::getFieldCode($field).'(name)', "type" => "|", "metadata" => "name", "index" => $i, 'qid'=>$field['qid']);
                        $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('File size').")",'code'=>viewHelper::getFieldCode($field).'(size)', "type" => "|", "metadata" => "size", "index" => $i);

                        //$fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
                    }
                }
                else
                {
                    $fnames[] = array($field['fieldname'], gT("File count"));
                }
            }

            $nfncount = count($fnames) - 1;
            if ($iId < 1)
            {
                $iId = 1;
            }

            $exist = SurveyDynamic::model($iSurveyID)->exist($iId);
            $next = SurveyDynamic::model($iSurveyID)->next($iId,true);
            $previous = SurveyDynamic::model($iSurveyID)->previous($iId,true);
            $aData['exist'] = $exist;
            $aData['next'] = $next;
            $aData['previous'] = $previous;
            $aData['id'] = $iId;

            $aViewUrls[] = 'browseidheader_view';
            if($exist)
            {
                $oPurifier=new CHtmlPurifier();
                //SHOW INDIVIDUAL RECORD
                $oCriteria = new CDbCriteria();
                if ($bHaveToken)
                {
                    $oCriteria = SurveyDynamic::model($iSurveyID)->addTokenCriteria($oCriteria);
                }

                $oCriteria->addCondition("id = {$iId}");
                $iIdresult = SurveyDynamic::model($iSurveyID)->findAllAsArray($oCriteria);
                foreach ($iIdresult as $iIdrow)
                {
                    $iId = $iIdrow['id'];
                    $rlanguage = $iIdrow['startlanguage'];
                }
                $aData['bHasFile']=false;
                if (isset($rlanguage))
                {
                    $aData['rlanguage'] = $rlanguage;
                }
                foreach ($iIdresult as $iIdrow)
                {
                    $highlight = false;
                    for ($i = 0; $i < $nfncount + 1; $i++)
                    {
                        if ($fnames[$i][0] != 'completed' && is_null($iIdrow[$fnames[$i][0]]))
                        {
                            continue;   // irrelevant, so don't show
                        }
                        $inserthighlight = '';
                        if ($highlight)
                            $inserthighlight = "class='highlight'";

                        if ($fnames[$i][0] == 'completed')
                        {
                            if ($iIdrow['submitdate'] == NULL || $iIdrow['submitdate'] == "N")
                            {
                                $answervalue = "N";
                            }
                            else
                            {
                                $answervalue = "Y";
                            }
                        }
                        else
                        {
                            if (isset($fnames[$i]['type']) && $fnames[$i]['type'] == "|")
                            {
                                $index = $fnames[$i]['index'];
                                $metadata = $fnames[$i]['metadata'];
                                $phparray = json_decode_ls($iIdrow[$fnames[$i][0]]);

                                if (isset($phparray[$index]))
                                {
                                    switch ($metadata)
                                    {
                                        case "size":
                                            $answervalue = sprintf(gT("%s KB"),intval($phparray[$index][$metadata]));
                                            break;
                                        case "name":
                                            $answervalue = CHtml::link(
                                                htmlspecialchars($oPurifier->purify(rawurldecode($phparray[$index][$metadata]))),
                                                $this->getController()->createUrl("/admin/responses",array("sa"=>"actionDownloadfile","surveyid"=>$surveyid,"iResponseId"=>$iId,"iQID"=>$fnames[$i]['qid'],"iIndex"=>$index))
                                            );
                                            break;
                                        default:
                                            $answervalue = htmlspecialchars(strip_tags(stripJavaScript($phparray[$index][$metadata])));
                                    }
                                    $aData['bHasFile']=true;
                                }
                                else
                                    $answervalue = "";
                            }
                            else
                            {
                                $answervalue = htmlspecialchars(strip_tags(stripJavaScript(getExtendedAnswer($iSurveyID, $fnames[$i][0], $iIdrow[$fnames[$i][0]], $sBrowseLanguage))), ENT_QUOTES);
                            }
                        }
                        $aData['answervalue'] = $answervalue;
                        $aData['inserthighlight'] = $inserthighlight;
                        $aData['fnames'] = $fnames;
                        $aData['i'] = $i;
                        $aViewUrls['browseidrow_view'][] = $aData;
                    }
                }
            }
            else
            {
                Yii::app()->session['flashmessage'] = gT("This response ID is invalid.");
            }

            $aViewUrls[] = 'browseidfooter_view';
            $aData['sidemenu']['state'] = false;
            $aData['menu']['edition'] = true;
            $aData['menu']['view'] = true;
            $aData['menu']['close'] =  true;
            // This resets the url on the close button to go to the upper view
            $aData['menu']['closeurl'] = $this->getController()->createUrl("admin/responses/sa/browse/surveyid/".$iSurveyId);

            $this->_renderWrappedTemplate('',$aViewUrls, $aData);
        }
        else
        {
            $aData = array();
            $aData['surveyid'] = $iSurveyID;
            $message = array();
            $message['title']= gT('Access denied!');
            $message['message']= gT('You do not have permission to access this page.');
            $message['class']= "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
    }

    public function index($iSurveyID)
    {
        $aData = $this->_getData($iSurveyID);
        extract($aData);
        $aViewUrls = array();

        /**
        * fnames is used as informational array
        * it containts
        *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
        */
        if (Yii::app()->request->getPost('sql'))
        {
            $aViewUrls[] = 'browseallfiltered_view';
        }

        $aData['num_total_answers'] = SurveyDynamic::model($iSurveyID)->count();
        $aData['num_completed_answers'] = SurveyDynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
        if (tableExists('{{tokens_' . $iSurveyID . '}}') && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read'))
        {
            $aData['with_token']= Yii::app()->db->schema->getTable('{{tokens_' . $iSurveyID . '}}');
            $aData['tokeninfo'] = Token::model($iSurveyID)->summary();
        }

        $aData['menu']['edition'] = false;

        $aViewUrls[] = 'browseindex_view';
        $this->_renderWrappedTemplate('',$aViewUrls, $aData);
    }


    /**
     * Change the value of the max characters to elipsize headers/questions in reponse grid.
     * It's called via ajax request
     */
    public function set_grid_display()
    {
        if (Yii::app()->request->getPost('state')=='extended')
        {
            Yii::app()->user->setState('responsesGridSwitchDisplayState','extended');
            Yii::app()->user->setState('defaultEllipsizeHeaderValue',1000);
            Yii::app()->user->setState('defaultEllipsizeQuestionValue',1000);
        }
        else
        {
            Yii::app()->user->setState('responsesGridSwitchDisplayState','compact');
            Yii::app()->user->setState('defaultEllipsizeHeaderValue',Yii::app()->params['defaultEllipsizeHeaderValue']);
            Yii::app()->user->setState('defaultEllipsizeQuestionValue',Yii::app()->params['defaultEllipsizeQuestionValue']);
        }
    }

    /**
     * Show responses for survey
     *
     * @param int $iSurveyId
     * @return void
     */
    public function browse($iSurveyId)
    {
        if(Permission::model()->hasSurveyPermission($iSurveyId,'responses','read'))
        {
            $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'listresponse.js');
            $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'tokens.js');

            // Basic datas for the view
            $aData                      = $this->_getData($iSurveyId);
            $aData['surveyid']          = $iSurveyId;
            $aData['menu']['edition']   = false;
            $aData['sidemenu']['state'] = false;
            $aData['issuperadmin']      = Permission::model()->hasGlobalPermission('superadmin');
            $aData['hasUpload']         = hasFileUploadQuestion($iSurveyId);
            $aData['fieldmap']          = createFieldMap($iSurveyId, 'full', true, false, $aData['language']);
            $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

            ////////////////////
            // Setting the grid

            // Basic variables
            $bHaveToken                 = $aData['surveyinfo']['anonymized'] == "N" && tableExists('tokens_' . $iSurveyId) && Permission::model()->hasSurveyPermission($iSurveyId,'tokens','read');// Boolean : show (or not) the token
            $aViewUrls                  = array('listResponses_view');
            $model                      =  SurveyDynamic::model($iSurveyId);

            // Reset filters from stats
            if (Yii::app()->request->getParam('filters') == "reset"){
                Yii::app()->user->setState('sql_'.$iSurveyId,'');
            }


            // Page size
            if (Yii::app()->request->getParam('pageSize')){
                Yii::app()->user->setState('pageSize',(int)Yii::app()->request->getParam('pageSize'));
            }

           
            if(Yii::app()->request->getParam('SurveyDynamic')){
                $model->setAttributes(Yii::app()->request->getParam('SurveyDynamic'),false);
            }

            $aVirtualFilters = array('completed_filter', 'firstname_filter', 'lastname_filter', 'email_filter');
            foreach($aVirtualFilters as $sFilterName) {
                $aParam=Yii::app()->request->getParam('SurveyDynamic');
                if(!empty($aParam[$sFilterName]))
                {
                    $model->$sFilterName = $aParam[$sFilterName];
                }
            }

            // rendering
            $aData['model']             = $model;
            $aData['bHaveToken']        = $bHaveToken;
            $aData['aDefaultColumns']   = $model->defaultColumns;            // Some specific columns
            $aData['pageSize']          = Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);      // Page size

            $this->_renderWrappedTemplate('responses', $aViewUrls, $aData);
        }
        else
        {
                Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
                $this->getController()->redirect(array('admin/survey','sa'=>'view','surveyid'=>$iSurveyId));
        }

    }

    /**
    * Saves the hidden columns for response browsing in the session
    *
    * @access public
    * @param $iSurveyID : survey id
    */

    public function setHiddenColumns($iSurveyId)
    {
        if(Permission::model()->hasSurveyPermission($iSurveyId,'responses','read'))
        {
           $aHiddenFields=explode('|',Yii::app()->request->getPost('aHiddenFields'));
           $_SESSION['survey_'.$iSurveyId]['HiddenFields']=$aHiddenFields;
        }
    }


    /**
    * Do an actions on response
    *
    * @access public
    * @param $iSurveyId : survey id
    * @return void
    */
    public function actionResponses($iSurveyId)
    {
        $action=Yii::app()->request->getPost('oper');
        $sResponseId=Yii::app()->request->getPost('id');
        switch ($action)
        {
            case 'downloadzip':
                $this->actionDownloadfiles($iSurveyId,$sResponseId);
                break;
            case 'del':
                $this->actionDelete($iSurveyId,$sResponseId);
                break;
            default:
                break;
        }
    }

    /**
    * Delete response
    * @access public
    * @param $iSurveyId : survey id
    * @param $sResponseId : list of response
    * @return void
    */
    public function actionDelete($surveyid)
    {
        $iSurveyId = (int) $surveyid;
        if (Permission::model()->hasSurveyPermission($iSurveyId,'responses','delete'))
        {
            $ResponseId  = ( Yii::app()->request->getPost('sItems') != '') ? json_decode(Yii::app()->request->getPost('sItems')):json_decode(Yii::app()->request->getPost('sResponseId'), true);
            $aResponseId = (is_array($ResponseId))?$ResponseId:array($ResponseId);

            foreach($aResponseId as $iResponseId)
            {
                $beforeDataEntryDelete = new PluginEvent('beforeDataEntryDelete');
                $beforeDataEntryDelete->set('iSurveyID',$iSurveyId);
                $beforeDataEntryDelete->set('iResponseID',$iResponseId);
                App()->getPluginManager()->dispatchEvent($beforeDataEntryDelete);

                Response::model($iSurveyId)->findByPk($iResponseId)->delete(true);
                $oSurvey=Survey::model()->findByPk($iSurveyId);
                if($oSurvey->savetimings == "Y"){// TODO : add it to response delete (maybe test if timing table exist)
                    SurveyTimingDynamic::model($iSurveyId)->deleteByPk($iResponseId);
                }
            }

            return $aResponseId;
        }
    }

    /**
    * Download individual file by response and filename
    *
    * @access public
    * @param $iSurveyId : survey id
    * @param $iResponseId : response if
    * @param $iQID : The question ID
    * @return application/octet-stream
    */
    public function actionDownloadfile($iSurveyId, $iResponseId, $iQID, $iIndex)
    {
        $iIndex=(int)$iIndex;
        $iResponseId=(int)$iResponseId;
        $iQID=(int)$iQID;

        if(Permission::model()->hasSurveyPermission($iSurveyId,'responses','read'))
        {
            $oResponse = Response::model($iSurveyId)->findByPk($iResponseId);
            $aQuestionFiles=$oResponse->getFiles($iQID);
            if (isset($aQuestionFiles[$iIndex]))
            {
               $aFile=$aQuestionFiles[$iIndex];
                $sFileRealName = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyId . "/files/" . $aFile['filename'];
                if (file_exists($sFileRealName))
                {
                    $mimeType=CFileHelper::getMimeType($sFileRealName, null, false);
                    if(is_null($mimeType)){
                        $mimeType="application/octet-stream";
                    }
                    @ob_clean();
                    header('Content-Description: File Transfer');
                    header('Content-Type: '.$mimeType);
                    header('Content-Disposition: attachment; filename="' . sanitize_filename(rawurldecode($aFile['name'])) . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($sFileRealName));
                    readfile($sFileRealName);
                    exit;
                }
            }
            Yii::app()->setFlashMessage(gT("Sorry, this file was not found."),'error');
            $this->getController()->redirect(array("admin/responses","sa"=>"browse","surveyid"=>$iSurveyId));
        }

    }

    /**
    * Construct a zip files from a list of response
    *
    * @access public
    * @param $iSurveyId : survey id
    * @param $sResponseId : list of response
    * @return application/zip
    */
    public function actionDownloadfiles($iSurveyId,$sResponseId)
    {
        if(Permission::model()->hasSurveyPermission($iSurveyId,'responses','read'))
        {
            if(!$sResponseId) // No response id : get all survey files
            {
                $oCriteria = new CDbCriteria();
                $oCriteria->select = "id";
                $oSurvey = SurveyDynamic::model($iSurveyId);
                $aResponseId = $oSurvey->getCommandBuilder()
                ->createFindCommand($oSurvey->tableSchema, $oCriteria)
                ->queryColumn();
            }
            else
            {
                $aResponseId=explode(",",$sResponseId);
            }
            if(!empty($aResponseId))
            {
                // Now, zip all the files in the filelist
                if(count($aResponseId)==1)
                    $zipfilename = "Files_for_survey_{$iSurveyId}_response_{$aResponseId[0]}.zip";
                else
                    $zipfilename = "Files_for_survey_{$iSurveyId}.zip";

                $this->_zipFiles($iSurveyId, $aResponseId, $zipfilename);
            }
            else
            {
                // No response : redirect to browse with a alert
                Yii::app()->setFlashMessage(gT("The requested files do not exist on the server."),'error');
                $this->getController()->redirect(array("admin/responses","sa"=>"browse","surveyid"=>$iSurveyId));
            }
        }
    }

    /**
     * Time statistics for responses
     *
     * @param int $iSurveyID
     * @return void
     */
    public function time($iSurveyID)
    {
        $aData = $this->_getData(array('iSurveyId' => $iSurveyID));


        $aData['columns'] = array(
            array(
                'header' => gT('ID'),
                'name' => 'id',
                'value'=> '$data->id',
                'headerHtmlOptions' => array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs')
            ),
            array(
                'header' => gT('Total time'),
                'name' => 'interviewtime',
                'value' => '$data->interviewtime'
            )
        );

        $fields = createTimingsFieldMap($iSurveyID, 'full',true,false,$aData['language']);
        foreach ($fields as $fielddetails)
        {
            // headers for answer id and time data
            if ($fielddetails['type'] == 'id')
            {
                $fnames[] = array($fielddetails['fieldname'], $fielddetails['question']);
            }

            if ($fielddetails['type'] == 'interview_time')
            {
                $fnames[] = array($fielddetails['fieldname'], gT('Total time'));
            }

            if ($fielddetails['type'] == 'page_time')
            {
                $fnames[] = array($fielddetails['fieldname'], gT('Group') . ": " . $fielddetails['group_name']);
                $aData['columns'][] = array(
                    'header' => gT('Group: ') . $fielddetails['group_name'],
                    'name' => $fielddetails['fieldname']
                );
            }

            if ($fielddetails['type'] == 'answer_time')
            {
                $fnames[] = array($fielddetails['fieldname'], gT('Question') . ": " . $fielddetails['title']);
                $aData['columns'][] = array(
                    'header' => gT('Question: ') . $fielddetails['title'],
                    'name' => $fielddetails['fieldname']
                );
            }
        }
        $fncount = count($fnames);

        $aViewUrls[] = 'browsetimeheader_view';
  
            $aViewUrls['browsetimerow_view'][] = $aData;
            /*
        }
        */

        // Set number of page
        if (Yii::app()->request->getParam('pageSize'))
        {
            Yii::app()->user->setState('pageSize',(int)Yii::app()->request->getParam('pageSize'));
        }


        //interview Time statistics
        $aData['model'] = SurveyTimingDynamic::model($iSurveyID);
        $aData['menu']['edition'] = false;

        $aData['pageSize'] = 10;
        $aData['statistics'] = SurveyTimingDynamic::model($iSurveyID)->statistics();
        $aData['num_total_answers'] = SurveyDynamic::model($iSurveyID)->count();
        $aData['num_completed_answers'] = SurveyDynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
        $aViewUrls[] = 'browsetimefooter_view';
        $this->_renderWrappedTemplate('', $aViewUrls, $aData);
    }

    /**
    * Supply an array with the responseIds and all files will be added to the zip
    * and it will be be spit out on success
    *
    * @param int $iSurveyID
    * @param array $responseIds
    * @param string $zipfilename
    * @return ZipArchive
    */
    private function _zipFiles($iSurveyID, $responseIds, $zipfilename)
    {
        /**
        * @todo Move this to model.
        */
        Yii::app()->loadLibrary('admin/pclzip');

        $tmpdir = Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR."surveys". DIRECTORY_SEPARATOR . $iSurveyID . DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR;

        $filelist = array();
        $responses = Response::model($iSurveyID)->findAllByPk($responseIds);
        $filecount = 0;
        foreach ($responses as $response)
        {
            foreach ($response->getFiles() as $file)
            {
                $filecount++;
                /*
                * Now add the file to the archive, prefix files with responseid_index to keep them
                * unique. This way we can have 234_1_image1.gif, 234_2_image1.gif as it could be
                * files from a different source with the same name.
                */
                if (file_exists($tmpdir . basename($file['filename'])))
                {
                    $filelist[] = array(PCLZIP_ATT_FILE_NAME => $tmpdir . basename($file['filename']),
                        PCLZIP_ATT_FILE_NEW_FULL_NAME => sprintf("%05s_%02s_%s", $response->id, $filecount, sanitize_filename(rawurldecode($file['name']))));
                }
            }
        }

        if (count($filelist) > 0)
        {
            $zip = new PclZip($tmpdir . $zipfilename);
            if ($zip->create($filelist) === 0)
            {
                //Oops something has gone wrong!
            }

            if (file_exists($tmpdir . '/' . $zipfilename))
            {
                @ob_clean();
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip, application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($zipfilename));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tmpdir . "/" . $zipfilename));
                readfile($tmpdir . '/' . $zipfilename);
                unlink($tmpdir . '/' . $zipfilename);
                exit;
            }
        }
        // No files : redirect to browse with a alert
        Yii::app()->setFlashMessage(gT("Sorry, there are no files for this response."),'error');
        $this->getController()->redirect(array("admin/responses","sa"=>"browse","surveyid"=>$iSurveyID));
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction='', $aViewUrls = array(), $aData = array())
    {
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'browse.js');
        $this->registerCssFile( 'PUBLIC', 'browse.css' );

        $iSurveyId = $aData['iSurveyId'];
        $aData['display']['menu_bars'] = false;
        $aData['display']['menu_bars']['browse'] = gT('Browse responses'); // browse is independent of the above
        $surveyinfo = Survey::model()->findByPk($iSurveyId)->surveyinfo;
        $aData["surveyinfo"] = $surveyinfo;
        $aData['title_bar']['title'] = gT('Browse responses').': '.$surveyinfo['surveyls_title'];
        parent::_renderWrappedTemplate('responses', $aViewUrls, $aData);
    }

}
