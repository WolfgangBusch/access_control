<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juli 2018
 */
require_once __DIR__.'/lib/class.access_control.php';
#
# --- proof the access on a media file (called via '/media/filename')
$file=rex_get('auth_file','string');
if(!empty($file)) access_control::print_file($file);   // display it
#
# --- proof the access on a media file (called via media manager)
$file=rex_get('rex_media_file','string');
if(!empty($file)) access_control::print_file($file);   // display it
?>
