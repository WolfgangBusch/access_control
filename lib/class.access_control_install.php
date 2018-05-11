<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Mai 2018
 */
#
class access_control_install {
#
function sql_action($sql,$query) {
   #   performing an SQL action using setQuery()
   #   including error message if fails
   #   $sql               SQL handle
   #   $query             SQL action
   #
   try {
        $sql->setQuery($query);
        $error="";
         } catch(rex_sql_exception $e) {
        $error=$e->getMessage();
        }
   if(!empty($error)) echo rex_view::error($error);
   }
function define_modul_out() {
   #   returning the module source code (output part)
   #
   $string=
'<?php
if(!rex::isBackend()):         // in frontend only
  #
  # --- Constants
  $username=rex_i18n::msg("access_control_user_name");
  $pwd     =rex_i18n::msg("access_control_password");
  $member=access_control::member_session("name");
  $mempwd=access_control::member_session("pwd");
  if(empty($member) or empty($mempwd))
    echo "<p class=\"access_control_error\">".
       rex_i18n::msg("access_control_configure_member")."</p>\n";
  #
  # --- get login name and password
  $login=$_POST["login"];
  $passwd=$_POST["passwd"];
  #
  # --- already authenticated?
  $loggedin=FALSE;
  $auth=access_control::user_logged_in();
  $user=$auth[session];
  if(!empty($user)):
    $login=$user;
    $loggedin=TRUE;
    else:
    #
    # --- analysing the input values
    $error="";
    if($passwd!=$mempwd) $error=
       rex_i18n::msg("access_control_wrong_password");
    if(empty($passwd))   $error=
       rex_i18n::msg("access_control_input_password");
    if($login!=$member)  $error=
       rex_i18n::msg("access_control_wrong_user_name");
    if(empty($login))    $error=
       rex_i18n::msg("access_control_input_user_password");
    if(empty($error)) $loggedin=TRUE;
    endif;
  if($loggedin):
    $error="<span class=\"access_control_success\">".
       rex_i18n::msg("access_control_user")."\"".$member."\" ".
       rex_i18n::msg("access_control_loggedin")."</span>";
    # --- set login SESSION variable
    access_control::member_session("set");
    endif;
  #
  # --- input form for User/password
  $art_id=rex_article::getCurrentId();
  $clang_id=rex_clang::getCurrentId();
  $self=rex_getUrl($art_id,$clang_id);
  echo "<div class=\"access_control_indent\">\n".
     "<div class=\"access_control_frame\">\n".
     "<p class=\"access_control_error\">".$error."</p>\n".
     "<form action=\"".$self."\" method=\"post\">\n".
     "<table>\n".
     "    <tr><td>".$username.": &nbsp;</td>\n".
     "        <td><input type=\"text\" name=\"login\" ".
     "value=\"".$login."\" ".
     "class=\"form-control access_control_width\" /></td>".
     "</tr>\n".
     "    <tr><td>".$pwd.":</td>\n".
     "        <td><input type=\"password\" name=\"passwd\" ".
     "value=\"".$passwd."\" ".
     "class=\"form-control access_control_width\" /></td>".
     "</tr>\n".
     "    <tr><td>&nbsp;</td>\n".
     "        <td><input class=\"form-control\" ".
     "type=\"submit\" value=\"anmelden\" /></td></tr>\n".
     "</table>\n".
     "</form>\n".
     "</div>\n".
     "</div>\n";
  endif;
?>';
   return str_replace("\\","\\\\",utf8_encode($string));
   }
function insert_module($mypackage) {
   #   creating the module (output part)
   #   functions used:
   #      self::define_modul_out()
   #      self::sql_action($sql,$query)
   #
   # --- module content (output)
   $out=self::define_modul_out();
   $sql=rex_sql::factory();
   $table="rex_module";
   $modname=utf8_encode(rex_i18n::msg("access_control_login_page")." (".$mypackage.")");
   #
   # --- module exists already?
   $query="SELECT * FROM ".$table." WHERE name LIKE '%".$mypackage."%'";
   $mod=$sql->getArray($query);
   if(count($mod[0])>0):
     #     existing:         update
     self::sql_action($sql,"UPDATE ".$table." SET output='".$out."' ".
        "WHERE id=".$mod[0][id]);
     else:
     #     not yet existing: insert
     self::sql_action($sql,"INSERT INTO ".$table." (name,input,output) ".
        "VALUES ('".$modname."','','".$out."')");
     endif;
   }
}
?>
