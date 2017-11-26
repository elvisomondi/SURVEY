<?php


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Authentication extends Survey_Common_Action
{

    
    public function index()
    {
        // The page should be shown only for non logged in users
        $this->_redirectIfLoggedIn();

        // Result can be success, fail or data for template
        $result = self::prepareLogin();

        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        $succeeded = isset($result[0]) && $result[0] == 'success';
        $failed = isset($result[0]) && $result[0] == 'failed';

        // If Ajax, echo success or failure json
        if ($isAjax) {
            Yii::import('application.helpers.admin.ajax_helper', true);
            if ($succeeded) {
                ls\ajax\AjaxHelper::outputSuccess(gT('Successful login'));
                return;
            }
            else if ($failed) {
                ls\ajax\AjaxHelper::outputError(gT('Incorrect username and/or password!'));
                return;
            }
        }
        // If not ajax, redirect to admin startpage or again to login form
        else {
            if ($succeeded) {
                self::doRedirect();
            }
            else if ($failed) {
                $message = $result[1];
                App()->user->setFlash('error', $message);
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            }
        }

        // Neither success nor failure, meaning no form submission - result = template data from plugin
        $aData = $result;

        // If for any reason, the plugin bugs, we can't let the user with a blank screen.
        $this->_renderWrappedTemplate('authentication', 'login', $aData);
    }

    /**
     * Prepare login and return result
     */
    public static function prepareLogin()
    {
        $aData = array();

        if (!class_exists('Authdb', false)) {
            $plugin = Plugin::model()->findByAttributes(array('name'=>'Authdb'));
            if (!$plugin) {
                $plugin = new Plugin();
                $plugin->name = 'Authdb';
                $plugin->active = 1;
                $plugin->save();
                App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
            } else {
                $plugin->active = 1;
                $plugin->save();
            }
        }

        $beforeLogin = new PluginEvent('beforeLogin');
        $beforeLogin->set('identity', new LSUserIdentity('', ''));
        App()->getPluginManager()->dispatchEvent($beforeLogin);

        /* @var $identity LSUserIdentity */
        $identity = $beforeLogin->get('identity');                              // Why here?

        // If the plugin private parameter "_stop" is false and the login form has not been submitted: render the login form
        if (!$beforeLogin->isStopped() && is_null(App()->getRequest()->getPost('login_submit')) )
        {
            
            if (!is_null($beforeLogin->get('default'))) {
                $aData['defaultAuth'] = $beforeLogin->get('default');
            }
            else {
                // THen, it checks if the the user set a different default plugin auth in application/config/config.php
                // eg: 'config'=>array()'debug'=>2,'debugsql'=>0, 'default_displayed_auth_method'=>'muh_auth_method')
                if (App()->getPluginManager()->isPluginActive(Yii::app()->getConfig('default_displayed_auth_method'))) {
                        $aData['defaultAuth'] = Yii::app()->getConfig('default_displayed_auth_method');
                    }else {
                        $aData['defaultAuth'] = 'Authdb';
                    }
            }

            $newLoginForm = new PluginEvent('newLoginForm');
            App()->getPluginManager()->dispatchEvent($newLoginForm);            // inject the HTML of the form inside the private varibale "_content" of the plugin
            $aData['summary'] = self::getSummary('logout');
            $aData['pluginContent'] = $newLoginForm->getAllContent();           // Retreives the private varibale "_content" , and parse it to $aData['pluginContent'], which will be  rendered in application/views/admin/authentication/login.php
        }else{
            $authMethod = App()->getRequest()->getPost('authMethod', $identity->plugin);      // If form has been submitted, $_POST['authMethod'] is set, else  $identity->plugin should be set, ELSE: TODO error
            $identity->plugin = $authMethod;

            $event = new PluginEvent('afterLoginFormSubmit');
            $event->set('identity', $identity);
            App()->getPluginManager()->dispatchEvent($event, array($authMethod));
            $identity = $event->get('identity');

            if ($identity->authenticate()){
                FailedLoginAttempt::model()->deleteAttempts();
                App()->user->setState('plugin', $authMethod);

                Yii::app()->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                Yii::app()->session['just_logged_in'] = true;
                Yii::app()->session['loginsummary'] = self::getSummary();

                $event = new PluginEvent('afterSuccessfulLogin');
                App()->getPluginManager()->dispatchEvent($event);

                return array('success');
            }else{
                // Failed
                $event = new PluginEvent('afterFailedLoginAttempt');
                $event->set('identity', $identity);
                App()->getPluginManager()->dispatchEvent($event);

                $message = $identity->errorMessage;
                if (empty($message)) {
                    // If no message, return a default message
                    $message = gT('Incorrect username and/or password!');
                }
                return array('failed', $message);
            }
        }

        return $aData;
    }

    /**
     * Logout user
     * @return void
     */
    public function logout()
    {
        /* Adding beforeLogout event */
        $beforeLogout = new PluginEvent('beforeLogout');
        App()->getPluginManager()->dispatchEvent($beforeLogout);
        regenerateCSRFToken();
        App()->user->logout();
        App()->user->setFlash('loginmessage', gT('Logout successful.'));

        /* Adding afterLogout event */
        $event = new PluginEvent('afterLogout');
        App()->getPluginManager()->dispatchEvent($event);

        $this->getController()->redirect(array('/admin/authentication/sa/login'));
    }

    /**
     * Forgot Password screen
     * @return void
     */
    public function forgotpassword()
    {
        $this->_redirectIfLoggedIn();

        if (!Yii::app()->request->getPost('action'))
        {
            $this->_renderWrappedTemplate('authentication', 'forgotpassword');
        }
        else
        {
            $sUserName = Yii::app()->request->getPost('user');
            $sEmailAddr = Yii::app()->request->getPost('email');

            $aFields = User::model()->findAllByAttributes(array('users_name' => $sUserName, 'email' => $sEmailAddr));

            // Preventing attacker from easily knowing whether the user and email address are valid or not (and slowing down brute force attacks)
            usleep(rand(Yii::app()->getConfig("minforgottenpasswordemaildelay"),Yii::app()->getConfig("maxforgottenpasswordemaildelay")));

            if (count($aFields) < 1 || ($aFields[0]['uid'] != 1 && !Permission::model()->hasGlobalPermission('auth_db','read',$aFields[0]['uid'])))
            {
                // Wrong or unknown username and/or email. For security reasons, we don't show a fail message
                $aData['message'] = '<br>'.gT('If username and email are valid and you are allowed to use internal database authentication a new password has been sent to you').'<br>';
            }
            else
            {
                $aData['message'] = '<br>'.$this->_sendPasswordEmail($sEmailAddr, $aFields).'</br>';
            }
            $this->_renderWrappedTemplate('authentication', 'message', $aData);
        }
    }

    /**
     * Send the forgot password email
     *
     * @param string $sEmailAddr
     * @param array $aFields
     */
    private function _sendPasswordEmail($sEmailAddr, $aFields)
    {
        $sFrom = Yii::app()->getConfig("siteadminname") . " <" . Yii::app()->getConfig("siteadminemail") . ">";
        $sTo = $sEmailAddr;
        $sSubject = gT('User data');
        $sNewPass = createPassword();
        $sSiteName = Yii::app()->getConfig('sitename');
        $sSiteAdminBounce = Yii::app()->getConfig('siteadminbounce');

        $username = sprintf(gT('Username: %s'), $aFields[0]['users_name']);
        $password = sprintf(gT('New password: %s'), $sNewPass);

        $body   = array();
        $body[] = sprintf(gT('Your user data for accessing %s'), Yii::app()->getConfig('sitename'));
        $body[] = $username;
        $body[] = $password;
        $body   = implode("\n", $body);

        if (SendEmailMessage($body, $sSubject, $sTo, $sFrom, $sSiteName, false, $sSiteAdminBounce))
        {
            User::model()->updatePassword($aFields[0]['uid'], $sNewPass);
            // For security reasons, we don't show a successful message
            $sMessage = gT('If username and email are valid and you are allowed to use internal database authentication a new password has been sent to you');
        }
        else
        {
            $sMessage = gT('Email failed');
        }

        return $sMessage;
    }

    /**
     * Get's the summary
     * @param string $sMethod login|logout
     * @param string $sSummary Default summary
     * @return string Summary
     */
    private static function getSummary($sMethod = 'login', $sSummary = '')
    {
        if (!empty($sSummary))
        {
            return $sSummary;
        }

        switch ($sMethod) {
            case 'logout' :
                $sSummary = gT('Please log in first.');
                break;

            case 'login' :
            default :
                $sSummary = '<br />' . sprintf(gT('Welcome %s!'), Yii::app()->session['full_name']) . '<br />&nbsp;';
                if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], 'logout') === FALSE)
                {
                    Yii::app()->session['metaHeader'] = '<meta http-equiv="refresh"'
                    . ' content="1;URL=' . Yii::app()->session['redirect_after_login'] . '" />';
                    $sSummary = '<p><font size="1"><i>' . gT('Reloading screen. Please wait.') . '</i></font>';
                    unset(Yii::app()->session['redirect_after_login']);
                }
                break;
        }

        return $sSummary;
    }

    /**
     * Redirects a logged in user to the administration page
     */
    private function _redirectIfLoggedIn()
    {
        if (!Yii::app()->user->getIsGuest())
        {
            $this->getController()->redirect(array('/admin'));
        }
    }

    /**
     * Check if a user can log in
     * @return bool|array
     */
    private function _userCanLogin()
    {
        $failed_login_attempts = FailedLoginAttempt::model();
        $failed_login_attempts->cleanOutOldAttempts();

        if ($failed_login_attempts->isLockedOut())
        {
            return $this->_getAuthenticationFailedErrorMessage();
        }
        else
        {
            return true;
        }
    }

    /**
     * Redirect after login
     * @return void
     */
    private static function doRedirect()
    {
        $returnUrl = App()->user->getReturnUrl(array('/admin'));
        Yii::app()->getController()->redirect($returnUrl);
    }

    /**
     * Renders template(s) wrapped in header and footer
     */
    protected function _renderWrappedTemplate($sAction = 'authentication', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}