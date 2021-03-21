<?php

/**
 * @copyright Copyright &copy; Daniel Orton, 2021
 * @package yii2-widgets
 * @subpackage yii2-widget-sidenav
 * @version 1.0.0
 */

namespace dan8551\sidenav;

use yii\web\AssetBundle;

class SideNavAsset extends AssetBundle
{
    public $sourcePath = '@vendor/dan8551/yii2-widget-sidenav/assets';
    
    public $css = [
        'css/simple-sidebar.css',
    ];
    
    public $depends = [
        'yii\bootstrap4\BootstrapAsset'
    ];
}
