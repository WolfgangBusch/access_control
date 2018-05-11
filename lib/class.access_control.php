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
     $_SESSION[$instname][$system_id]['STAMP']=time();
     return;
     endif;
   #
   # --- return member user's user name if he is logged in
   if($func=="get")
     return $_SESSION[$instname][$system_id]['MEMBER_LOGIN'];
   #
   # --- return member user's user name or password
   if($func=="name")
     return $member_login;
   if($func=="pwd")
     return $member_password;
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
   #   determine if access to an article needs to be denied,
   #   return value:
   #      1, if the article is located in the protected area and the
   #         visitor is not authenticated
   #      2, if the article is located in the forbidden area and the
   #         visitor is not authenticated as site adminstrator
   #   used functions:
   #      self::please_login()
   #      self::stay_out()
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
   if(self::no_access(1)) $rc=1;  // protected area
   if(self::no_access(2)) $rc=2;  // forbidden area
   return $rc;
   }
function no_access($kont) {
   #   determine if access to an article needs to be denied,
   #   return value:
   #      TRUE,  if the article is located in the protected or in the
   #             forbidden area respectively, and the visitor is not
   #             authenticated as authorized user
   #      FALSE, otherwise, or no protected area or no forbidden area
   #             is configurated, respectively
   #   $kont              <=1: proof the access on the protected area
   #                      >=2: proof the access on the forbidden area
   #
   if($kont<=1):
     $cat_id=rex_config::get('access_control','cat_protected_id');
     else:
     $cat_id=rex_config::get('access_control','cat_forbidden_id');
     endif;
   $stayout=FALSE;
   if(!empty($cat_id)):
     #
     # --- current article in protected / forbidden area?
     $art=rex_article::getCurrent();
     $noacc=FALSE;
     if($art->getId()==$cat_id) $noacc=TRUE;
     $arr=explode("|",$art->getValue("path"));
     for($i=1;$i<count($arr)-1;$i=$i+1)
        if($arr[$i]==$cat_id) $noacc=TRUE;
     #
     # --- article in protected / forbidden area, is the visitor authenticated?
     if($noacc):
       if($kont<=1):
         $auth=self::user_logged_in();
         if(empty($auth[redaxo]) and empty($auth[ycom]) and empty($auth[session])):
           $stayout=TRUE;
           endif;
         else:
         $uid=0;
         if(rex_backend_login::createUser()) $uid=rex::getUser()->getId();
         if($uid!=1):
           $stayout=TRUE;
           endif;
         endif;
       endif;
     endif;
   return $stayout;
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
function print_file($file) {
   #   displaying a media file, if no access is allowed a general error
   #   file ('protected.gif') is displayed instead
   #   used functions:
   #      self::user_logged_in()
   #      self::media2protect($file)
   #
   if(rex::isBackend() or empty($file) or
      !file_exists(rex_path::media($file))) return;
   #
   $auth=self::user_logged_in();
   if(self::media2protect($file) and
      empty($auth[redaxo]) and empty($auth[ycom]) and empty($auth[session])):
     $media=rex_path::addonAssets('access_control','protected.gif');
     $managed_media=new rex_managed_media($media);
     else:
     $managed_media=new rex_managed_media(rex_path::media($file));
     endif;
   (new rex_media_manager($managed_media))->sendMedia();
   }
}
?>
