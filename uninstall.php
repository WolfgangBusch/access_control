<?php
/*
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2022
 */
$my_package=$this->getPackageId();
$dir=rex_path::addonCache($my_package);
$file=rex_path::addonCache($my_package,$my_package::cache_file);
unlink($file);
rmdir($dir);
?>