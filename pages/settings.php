<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2018
 */
$stx='style="white-space:nowrap;"';
$sty='style="padding-left:20px; white-space:nowrap;"';
#
# --- read the inserted values
$forb_id   =$_POST['forb_id'];
$prot_id   =$_POST['prot_id'];
$protmed_id=$_POST['protmed_id'];
if(!empty($protmed_id) and intval($protmed_id)<=0):
  $media=rex_media::get($protmed_id);
  $protmed_id=$media->getCategoryId();
  endif;
$memb_login=$_POST['memb_login'];
$memb_pwd  =$_POST['memb_pwd'];
#
# --- read the configuration parameters
if($forb_id<=0)        $forb_id   =rex_config::get('access_control','cat_forbidden_id');
if($prot_id<=0)        $prot_id   =rex_config::get('access_control','cat_protected_id');
if($protmed_id<=0)     $protmed_id=rex_config::get('access_control','medcat_protected_id');
if(empty($memb_login)) $memb_login=rex_config::get('access_control','member_login');
if(empty($memb_pwd))   $memb_pwd  =rex_config::get('access_control','member_password');
#
# --- define input buttons and fields
$input_forb_id   =rex_var_link::getWidget(1,'forb_id',$forb_id);
$input_prot_id   =rex_var_link::getWidget(2,'prot_id',$prot_id);
$input_protmed_id=rex_var_media::getWidget(1,'protmed_id',$protmed_id);
$input_memb_login='<input class="form-control" type="text" name="memb_login" value="'.$memb_login.'" />';
$input_memb_pwd  ='<input class="form-control" type="password" name="memb_pwd" value="'.$memb_pwd.'" />';
### the buttons HTML codes contain:
### <input type="hidden" name="forb_id" id="REX_LINK_1" value="***" />
### <input type="hidden" name="prot_id" id="REX_LINK_2" value="***" />
### <input class="form-control" type="text" name="protmed_id" value="***" id="REX_MEDIA_1" readonly />
#
# --- protected categories and media categories
$string='
<form method="post">
<table style="background-color:inherit;">
    <tr><td colspan="3">
            '.rex_i18n::msg("access_control_settings_par1").'</td></tr>
    <tr><td '.$stx.'></td>
        <td '.$sty.'><small>'.rex_i18n::msg("access_control_settings_col12").'</small></td>
        <td '.$sty.'><small>'.rex_i18n::msg("access_control_settings_col13").'</small></td></tr>
    <tr><td '.$stx.'>'.rex_i18n::msg("access_control_settings_col21").':</td>
        <td '.$sty.'>'.$input_prot_id.'</td>
        <td '.$sty.'>'.rex_i18n::msg("access_control_settings_col23").'</td></tr>
    <tr><td '.$stx.'>'.rex_i18n::msg("access_control_settings_col31").':</td>
        <td '.$sty.'>'.$input_protmed_id.'</td>
        <td '.$sty.'>'.rex_i18n::msg("access_control_settings_col33").'</td></tr>
    <tr><td '.$stx.'>'.rex_i18n::msg("access_control_settings_col41").' (*):</td>
        <td '.$sty.'>'.$input_forb_id.'</td>
        <td '.$sty.'>'.rex_i18n::msg("access_control_settings_col43").'</td></tr>
    <tr><td></td>
        <td colspan="2" '.$sty.'>(*)<small> &nbsp; '.rex_i18n::msg("access_control_settings_col52").'</small></td></tr>
    <tr><td colspan="3">';
echo $string;
#
# --- access data for the member user
$string='
    <tr><td colspan="3"><br/>
            '.rex_i18n::msg("access_control_settings_par2").'</td></tr>
    <tr><td '.$stx.'>'.rex_i18n::msg("access_control_user_name").':</td>
        <td '.$sty.'>'.$input_memb_login.'</td>
        <td '.$sty.'></td></tr>
    <tr><td '.$stx.'>'.rex_i18n::msg("access_control_password").':</td>
        <td '.$sty.'>'.$input_memb_pwd.'</td>
        <td '.$sty.'></td></tr>
</table><br/>
<button class="btn btn-save" type="submit" name="sendit" value="sendit"
        title="'.rex_i18n::msg("access_control_settings_title").'"> '.rex_i18n::msg("access_control_settings_text").' </button>
</form>';
echo $string;
#
# --- save the new configuration parameters (after submit)
if(!empty($_POST['sendit'])):
  rex_config::set('access_control','cat_forbidden_id',   intval($forb_id));
  rex_config::set('access_control','cat_protected_id',   intval($prot_id));
  rex_config::set('access_control','medcat_protected_id',intval($protmed_id));
  rex_config::set('access_control','member_login',       $memb_login);
  rex_config::set('access_control','member_password',    $memb_pwd);
  endif;
?>
