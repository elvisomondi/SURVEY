<?php
    /**
     * This widget render a drop-up button for grid massive actions, and attach modals to the actions.
     */
    class massiveActionsWidget extends CWidget
    {
        // Selector variables
        public $pk;                         // The primary key of the grid
        public $gridid;                     // The id of the related grid
        public $dropupId      = 'dropupId'; // The wanted ID for the dropup button
        public $dropUpText    = '';         // The wanted text for the dropup button
        public $aActions;                   // Array of actions

        public function run()
        {
            // Render the selector
            $this->render('selector');

            // Render the modal for each action
            foreach($this->aActions as $key => $aAction)
            {
                // Not all action require a modal (eg: downloads, etc)
                if( isset($aAction['actionType']) && $aAction['actionType']=='modal')
                {
                    // Modal type define the view to render in views/modal
                    if ($this->isView($aAction['modalType']))
                    {
                        //TODO: common view for all modal types.
                        $this->render(
                            'modals/'.$aAction['modalType'],array(
                                'aAction' => $aAction,
                                'key'     => $key,
                            )
                        );
                    }
                    else
                    {
                        // We could rather raise an exception.
                        $this->render('unknown_modal_type');
                    }
                }
            }

            // The error modal rendered if no item is selected in the grid
            $this->render('modals/first-select',array());

            Yii::app()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/listActions.js'));
        }

        private function isView($display)
        {
            return in_array($display, array('yes-no', 'empty'));
        }
    }
