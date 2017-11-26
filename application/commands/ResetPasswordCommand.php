<?php
   
    class ResetPasswordCommand extends CConsoleCommand
    {
        public $connection;

        public function run($sArgument)
        {
            if (isset($sArgument) && isset($sArgument[0]) && isset($sArgument[1]))
            {
                $iUserID=User::model()->getID($sArgument[0]);
                if ($iUserID)
                {
                  User::model()->updatePassword($iUserID,$sArgument[1]);
                  echo "Password for user {$sArgument[0]} was set.\n";
                }
                else
                {
                    echo "User {$sArgument[0]} not found.\n";
                }

            }
            else
            {
                //TODO: a valid error process
                echo 'You have to set username and password on the command line like this: php console.php username password';
            }
        }
    }

?>
