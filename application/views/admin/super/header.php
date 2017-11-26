<?php
/**
 * Header of the application
 * Called from render_wrapped_template
 */
?>
<!DOCTYPE html>
<html lang="<?php echo str_replace('-informal','',$adminlang); ?>"<?php echo $languageRTL;?> >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Assets load -->


    <?php
        $oAdminTheme = AdminTheme::getInstance();
        $oAdminTheme->registerStylesAndScripts();
    ?>
    <?php if(!YII_DEBUG ||  Yii::app()->getConfig('use_asset_manager')): ?>
        <!-- Debug mode is off, so the asset manager will be used-->
    <?php else: ?>
        <!-- Debug mode is on, so the asset manager will not be used -->
    <?php endif; ?>

    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    
    <?php echo $firebug ?>
    <?php $this->widget('ext.LimeScript.LimeScript'); ?>
    <?php $this->widget('ext.LimeDebug.LimeDebug'); ?>
</head>
<body>

<!-- Loading wrapper -->
<div id='ls-loading'>
    <span id='ls-loading-spinner' class='fa fa-spinner fa-spin fa-4x'></span>
</div>

<?php $this->widget('ext.FlashMessage.FlashMessage'); ?>

<script type='text/javascript'>
var frameSrc = "/login";
    <?php if(isset($formatdata)):?>
    var userdateformat='<?php echo $formatdata['jsdate']; ?>';
    var userlanguage='<?php echo $adminlang; ?>';
    <?php endif; ?>
</script>
