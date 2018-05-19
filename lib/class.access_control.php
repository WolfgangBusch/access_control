<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Mai 2018
 */
#
class access_control {
#
function member_session($func) {
   #   Returning different values depending on the value of $func
   #   $func=
   #      "set":  authenticate (log in) the member user,
   #              i.e. set the appropriate session variable,
   #              return value ""
   #      "end":  log out the member user,
   #              i.e. empty the appropriate session variable,
   #              return value ""
   #      "get":  return user name of the member user (= value of the
   #              session variable, if he is logged in)
   #              return value "" (otherwise)
   #      "name": return the member user's user name (as configurated)
   #              return "", if not configured
   #      "pwd":  return the member user's password (as configurated)
   #              return "", if not configured
   #
   $member_login   =rex_config::get('access_control','member_login');
   $member_password=rex_config::get('access_control','member_password');
   #
   # --- not configured
   if(empty($member_login) or empty($member_password)) return;
   $instname=rex::getProperty('instname');
   $system_id=rex_backend_login::SYSTEM_ID;
   #
   # --- set session variable
   if($func=="set"):
     $_SESSION[$instname][$system_id]['MEMBER_LOGIN']=$member_login;
     return;
     endif;
   # --- empty session variable
   if($func=="end"):
     $_SESSION[$instname][$system_id]['MEMBER_LOGIN']="";
     return;
     endif;
   #
   # --- return member user's user name if he is logged in
   if($func=="get")
     return $_SESSION[$instname][$system_id]['MEMBER_LOGIN'];
   #
   # --- return member user's user name or password
   if($func=="name") return $member_login;
   if($func=="pwd")  return $member_password;
   }
function get_rex_editor() {
   #   returns die Redaxo editor's user name, if he is logged in
   #   (or "" if not)
   #
   $user="";
   if(rex_backend_login::createUser()):
     $user=rex::getUser()->getValue("login");
     return $user;
     endif;
   return $user;
   }
function user_logged_in() {
   #   check if the visitor is logged in one of the following user:
   #   as Redaxo editor or as member user or as YCom user,
   #   return an associative array containing:
   #      $auth[redaxo]  = Redaxo editor's user name (or "")
   #      $auth[session] = member's user name (or "")
   #      $auth[ycom]    = YCom user's user name (or "")
   #   used functions:
   #      self::get_rex_editor()
   #      self::member_session("get")
   #
   # --- authenticated as Redaxo editor?
   $auth[redaxo]=self::get_rex_editor();
   #
   # --- authenticated as member user?
   $auth[session]=self::member_session("get");
   #
   # --- authenticated as YCom user?
   $auth[ycom]="";
   if(rex_addon::get('ycom')->isAvailable()):
     rex_ycom::addTable('rex_ycom_user');
     rex_yform_manager_dataset::setModelClass('rex_ycom_user', rex_ycom_user::class);
     rex_ycom_auth::login([]);
     $us=rex_ycom_auth::getUser();
     if($us!=NULL) $auth[ycom]=rex_ycom_user::get($us->getId())->login;
     endif;
   #
   return $auth;
   }
function protected_or_forbidden() {
   #   determine if access to the current article needs to be denied,
   #   return value:
   #      1, if the article is located in the protected area and the
   #         visitor is not authenticated
   #      2, if the article is located in the forbidden area and the
   #         visitor is not authenticated as site adminstrator
   #   used functions:
   #      self::no_access($art,$kont)
   #
   #   exemplary section of the page template, show an error message
   #   instead of the article content
   #      ...
   #      $rc=access_control::protected_or_forbidden();
   #      if($rc>0):
   #        if($rc==1):
   #          echo "... protected ...";
   #          else:
   #          echo "... forbidden ...";
   #          endif;
   #        else:
   #        echo $this->getArticle(); // show the article content
   #        endif;
   #      ...
   #
   $rc=0;
   $art=rex_article::getCurrent();
   if(self::no_access($art,1)<=0) $rc=1;  // protected area
   if(self::no_access($art,2)<=0) $rc=2;  // forbidden area
   return $rc;
   }
function no_access($art,$kont) {
   #   determine if an article is located in a protected or forbidden
   #   area, respectively, and if access is allowed or to be denied
   #   $art               given article object
   #   $kont              <=1: proof the access on the protected area
   #                      >=2: proof the access on the forbidden area
   #   return value:
   #      1: no protected/forbidden area is configured
   #      2: the article is not located in the protected/forbidden area
   #      3: the article is located in the protected/forbidden area,
   #         but the visitor is authenticated as authorized user
   #      0: the article is located in the protected/forbidden area,
   #         and the visitor is not authenticated as authorized user
   #         access is to be denied
   #   used functions:
   #      self::user_logged_in()
   #
   $rc=1;
   if($kont<=1):
     $cat_id=rex_config::get('access_control','cat_protected_id');
     else:
     $cat_id=rex_config::get('access_control','cat_forbidden_id');
     endif;
   if(!empty($cat_id)):
     #
     # --- article in protected / forbidden area?
     $rc=2;
     if($art->getId()==$cat_id) $rc=3;
     $arr=explode("|",$art->getValue("path"));
     for($i=1;$i<count($arr)-1;$i=$i+1)
        if($arr[$i]==$cat_id) $rc=3;
     #
     # --- article in protected / forbidden area, is the visitor authenticated?
     if($rc==3):
       if($kont<=1):
         $auth=self::user_logged_in();
         if(empty($auth[redaxo]) and empty($auth[ycom]) and empty($auth[session])):
           $rc=0;
           endif;
         else:
         $uid=0;
         if(rex_backend_login::createUser()) $uid=rex::getUser()->getId();
         if($uid!=1):
           $rc=0;
           endif;
         endif;
       endif;
     endif;
   return $rc;
   }
function media2protect($file) {
   #   determine if access to a media file is to be denied,
   #   return value:
   #      TRUE,  if the media file belongs to the protected media category
   #             or to one of its subcategories
   #      FALSE, otherwise, or no protected category is configurated
   #   $file              given media file (relative file name)
   #
   $medcat_protected_id=rex_config::get('access_control','medcat_protected_id');
   if(empty($medcat_protected_id)) return FALSE;
   $media=rex_media::get($file);
   #
   # --- file does not exist
   if($media==NULL) return FALSE;
   #
   # --- determine the top parent media category
   $cat=$media->getCategory();
   $catid=$cat->getPathAsArray()[0];
   if($catid<=0) $catid=$cat->getId();
   #
   # --- top parent media category = protected media category?
   if($catid==$medcat_protected_id) return TRUE;
   return FALSE;
   }
function sendFile($file,$contentType,$contentDisposition='inline') {
   #   modified Redaxo rex_response::sendFile(...)
   #   this version always delivers the original file content, no cache is used
   #   used functions:
   #      rex_response::cleanOutputBuffers()
   #      rex_response::sendContentType($contentType)
   #
   rex_response::cleanOutputBuffers();
   if(!file_exists($file)):
     header('HTTP/1.1 '.rex_response::HTTP_NOT_FOUND);
     exit;
     endif;
   session_write_close();
   rex_response::sendContentType($contentType);
   header('Content-Disposition: '.$contentDisposition.'; filename="'.basename($file).'"');
   if(!ini_get('zlib.output_compression'))
     header('Content-Length: '.filesize($file));
   readfile($file);
   }
function print_file($file) {
   #   displaying a media file, if no access is allowed a general error
   #   file ('protected.gif') is displayed instead
   #   used functions:
   #      self::user_logged_in()
   #      self::media2protect($file)
   #      self::sendFile($medfile,$type,$contentDisposition)
   #
   if(rex::isBackend() or empty($file) or
      !file_exists(rex_path::media($file))) return;
   #
   $auth=self::user_logged_in();
   if(self::media2protect($file) and
      empty($auth[redaxo]) and empty($auth[ycom]) and empty($auth[session])):
     #     error file displayed
     $errfile=rex_path::addonAssets('access_control','protected.gif');
     $managed_media=new rex_managed_media($errfile);
     (new rex_media_manager($managed_media))->sendMedia();
     else:
     $medfile=rex_path::media($file);
     $type=mime_content_type($medfile);
     if(substr($type,0,5)=="image" or $type="text/plain" or $type=="application/pdf"):
       #     images and pdf displayed by sendMedia()
       $managed_media=new rex_managed_media($medfile);
       (new rex_media_manager($managed_media))->sendMedia();
       else:
       #     files downloaded by sendFile(), accounting large files
       self::sendFile($medfile,$type,'attachment');
       endif;
     endif;
   }
function login_page() {
   #   Displaying a login page for a visitor to get authenticated
   #   used functions:
   #      self::member_session("name");
   #      self::member_session("pwd");
   #      self::user_logged_in();
   #      self::member_session("set");
   #
   if(rex::isBackend()):
     echo "<p>".rex_i18n::msg("access_control_login_page_backend")."</p>\n";
     else:
     #
     # --- Constants
     $username=rex_i18n::msg("access_control_user_name");
     $pwd     =rex_i18n::msg("access_control_password");
     $member=self::member_session("name");
     $mempwd=self::member_session("pwd");
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
     $auth=self::user_logged_in();
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
          rex_i18n::msg("access_control_user")." \"".$member."\" ".
          rex_i18n::msg("access_control_loggedin")."</span>";
       # --- set login SESSION variable
       self::member_session("set");
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
   }
}
?>
