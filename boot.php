<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2019
 */
require_once __DIR__.'/lib/class.access_control.php';
#
# --- proof the access on a media file (called via '/media/filename')
$file=rex_get('auth_file','string');
if(!empty($file)) access_control::print_file($file,'');   // display it
#
# --- proof the access on a media file (called via media manager)
$file=rex_get('rex_media_file','string');
$type=rex_get('rex_media_type','string');
if(!empty($file)) access_control::print_file($file,$type);   // display it
#
# --- include the stylesheet file in backend, too
$my_package=$this->getPackageId();
$file=rex_url::addonAssets($my_package).$my_package.'.css';
rex_view::addCssFile($file);
?>