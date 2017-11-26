<?php
/**
* Configuration menu. rendered from adminmenu
* @var $userscount
*/

//Todo : move to controller
?>

<!-- Configuration -->
<?php if(Permission::model()->hasGlobalPermission('superadmin','read')
            || Permission::model()->hasGlobalPermission('templates','read')
            || Permission::model()->hasGlobalPermission('labelsets','read')
            || Permission::model()->hasGlobalPermission('users','read')
            || Permission::model()->hasGlobalPermission('usergroups','read')
            || Permission::model()->hasGlobalPermission('participantpanel','read')
            || Permission::model()->hasGlobalPermission('settings','read') ): ?>

<li class="dropdown mega-dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="icon-settings" ></span>
        <?php eT('Configuration');?>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu mega-dropdown-menu" id="mainmenu-dropdown">

        <!-- System overview -->
        <li class="col-sm-3">

            <!-- System overview -->
            <?php if(Permission::model()->hasGlobalPermission('superadmin','read')): ?>
                <div class="box" id="systemoverview">
                    <div class="box-icon">
                        <span class="glyphicon glyphicon-info-sign" id="info-header"></span>
                    </div>
                    <div class="info">
                        <h5 class="text-center"><?php eT("System overview"); ?></h5>
                        <dl class="dl-horizontal">
                            <dt class="text-info"><?php eT('Users');?></dt>
                            <dd><?php echo $userscount;?></dd>
                            <dt class="text-info"><?php eT('Surveys');?></dt>
                            <dd><?php echo $surveyscount; ?></dd>
                            <dt class="text-info"><?php eT('Active surveys');?></dt>
                            <dd><?php echo $activesurveyscount; ?></dd>
                            

                        </dl>
                    </div>
                </div>
            <?php endif; ?>
        </li>

        <!-- Expression Manager -->
        
        <!-- Advanced -->

        <li class="col-sm-2">
            <ul>

                <!-- Advanced -->
                <li class="dropdown-header">
                    <span class="icon-tools" ></span>
                    <?php eT('Advanced');?>
                </li>
                <?php if(Permission::model()->hasGlobalPermission('templates','read')): ?>
                    <!-- Template Editor -->
                    
                    <?php endif;?>
                <?php if(Permission::model()->hasGlobalPermission('labelsets','read')): ?>
                    <!-- Edit label sets -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>">
                            <?php eT("Manage label sets");?>
                        </a>
                    </li>
                    <?php endif;?>

                <!-- Check Data Integrity -->
                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')): ?>

                   

                    <!-- Backup Entire Database -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/dumpdb"); ?>">
                            <?php eT("Backup entire database");?>
                        </a>
                    </li>

                <?php endif;?>

                <!-- Comfort update -->
                <?php if(Permission::model()->hasGlobalPermission('superadmin')): ?>
                    
                <?php endif;?>
            </ul>

        </li>

        <!-- Users -->
        <li class="col-sm-2">

            <!-- Users -->
            <ul>

                <!-- Users -->
                <li class="dropdown-header">

                    <span class="icon-user" ></span>
                    <?php eT('Users');?>
                </li>

                <!-- Manage survey administrators -->
                <?php if(Permission::model()->hasGlobalPermission('users','read')): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/user/sa/index"); ?>">
                            <?php eT("Manage survey administrators");?>
                        </a>
                    </li>
                    <?php endif;?>
                <?php if(Permission::model()->hasGlobalPermission('usergroups','read')): ?>

                    <!-- Create/edit user groups -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/usergroups/sa/index"); ?>">
                            <?php eT("Create/edit user groups");?>
                        </a>
                    </li>

                    <?php endif;?>

                <!-- Central participant database -->
                <?php if(Permission::model()->hasGlobalPermission('participantpanel','read')): ?>

                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>">
                            <?php eT("Central participant database"); ?>
                        </a>
                    </li>
                    <?php endif;?>
            </ul>
        </li>



        <!-- Settings -->

        <li class="col-sm-2">
            <ul>

                <!-- Settings -->
                <li class="dropdown-header">
                    <span class="icon-global" ></span>
                    <?php eT('Settings');?>
                </li>

                <?php if(Permission::model()->hasGlobalPermission('settings','read')): ?>
                    <!-- Home page settings -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/homepagesettings"); ?>">
                            <?php eT("Home page settings");?>
                        </a>
                    </li>

                    <!-- Global settings -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/globalsettings"); ?>">
                            <?php eT("Global settings");?>
                        </a>
                    </li>

                    
                <?php endif;?>

            </ul>
        </li>
    </ul>
</li>
<?php endif;?>
