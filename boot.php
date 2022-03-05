<?php
/*
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Maerz 2022
 */
require_once __DIR__.'/lib/class.access_control.php';
#
# --- proof the access on a media file called via media manager
#     (RewriteRule for media files called via '/media/filename' needed)
$file=rex_get('rex_media_file','string');
if(!empty($file)) access_control::control_file($file);
#
# --- include the stylesheet file in backend, too
$my_package=$this->getPackageId();
$file=rex_url::addonAssets($my_package).$my_package.'.css';
rex_view::addCssFile($file);
?>