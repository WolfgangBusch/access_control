<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Mai 2018
 */
require_once __DIR__.'/lib/class.access_control.php';
#
# --- generate the Login Page Module
require_once __DIR__.'/lib/class.access_control_install.php';
access_control_install::insert_module($this->getPackageId());
?>