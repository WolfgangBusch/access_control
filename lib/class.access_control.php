<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juli 2019
 */
#
define('CONTROL',          $this->getPackageId());  // Package-Id
define('CAT_PROTECTED',    'cat_protected_id');
define('CAT_FORBIDDEN',    'cat_forbidden_id');
define('MEDCAT_PROTECTED', 'medcat_protected_id');
define('MEMBER_LOGIN',     'member_login');
define('MEMBER_PASSWORD',  'member_password');
define('CLASS_INDENT',     'access_control_indent');  // CSS classes
define('CLASS_NOWRAP',     'access_control_nowrap');
#
class access_control {
#
public static function member_session($func) {
   #   Returning different values depending on the value of $func
   #   $func=
   #      'set'   authenticate (sign in) the member user,
   #              i.e. set the appropriate session variable,
   #              return value ''
   #      'end'   sign off the member user,
   #              i.e. delete the appropriate sssion variable,
   #              return value ''
   #      'get'   return user name of the member user (= value of the
   #              session variable, if he is authenticated)
   #              return value '' (otherwise)
   #      'name'  return the member user's user name (as configurated)
   #              return '', if not configured
   #      'pwd'   return the member user's password (as configurated)
   #              return '', if not configured
   #
   $member_login   =rex_config::get(CONTROL,MEMBER_LOGIN);
   $member_password=rex_config::get(CONTROL,MEMBER_PASSWORD);
   #
   # --- not configured
   if(empty($member_login) or empty($member_password)) return;
   #
   # --- set session variable
   if($func=='set'):
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     $_SESSION[CONTROL]=$member_login;
     return;
     endif;
   # --- delete session variable
   if($func=='end'):
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     $_SESSION[CONTROL]='';
     return;
     endif;
   #
   # --- return member user's user name if he is authenticated
   if($func=='get'):
     $ses='';
     if(!empty($_SESSION[CONTROL])) $ses=$_SESSION[CONTROL];
     return $ses;
     endif;
   #
   # --- return member user's user name or password
   if($func=='name') return $member_login;
   if($func=='pwd')  return $member_password;
   }
public static function get_rex_editor() {
   #   returns the Redaxo editor's user name, if he is authenticated
   #   (or '' if not)
   #
   $user='';
   if(rex_backend_login::createUser()):
     $user=rex::getUser()->getValue('login');
     return $user;
     endif;
   return $user;
   }
public static function user_authenticated() {
   #   check if the visitor is authenticated:
   #   as Redaxo editor and/or as member user,
   #   return an associative array containing:
   #      $auth['redaxo']  = Redaxo editor's user name (or '')
   #      $auth['session'] = member's user name (or '')
   #   used functions:
   #      self::get_rex_editor()
   #      self::member_session('get')
   #
   # --- authenticated as Redaxo editor?
   $auth['redaxo']=self::get_rex_editor();
   #
   # --- authenticated as member user?
   $auth['session']=self::member_session('get');
   #
   return $auth;
   }
public static function protected_or_forbidden() {
   #   determine if access to the current article needs to be denied,
   #   return value:
   #      0, if access is allowed
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
   #          echo '... protected ...';
   #          else:
   #          echo '... forbidden ...';
   #          endif;
   #        else:
   #        echo $this->getArticle(); // show the article content
   #        endif;
   #      ...
   #
   $art=rex_article::getCurrent();
   $rc=0;
   if(self::no_access($art,1)<=0) $rc=1;  // protected area
   if(self::no_access($art,2)<=0) $rc=2;  // forbidden area
   return $rc;
   }
public static function no_access($art,$kont) {
   #   determine if an article is located in a protected or forbidden
   #   area, respectively, and if access is allowed or to be denied
   #   $art               given article object
   #   $kont              <=1: proof the access on the protected area
   #                      >=2: proof the access on the forbidden area
   #   return value:
   #      1: protected/forbidden area not configured
   #      2: the article is not located in the protected/forbidden area
   #      3: the article is located in the protected/forbidden area,
   #         but the visitor is authenticated as authorized user
   #      0: the article is located in the protected/forbidden area,
   #         and the visitor is not authenticated as authorized user,
   #         access is to be denied
   #   used functions:
   #      self::user_authenticated()
   #
   $rc=1;
   if($kont<=1):
     $cat_id=rex_config::get(CONTROL,CAT_PROTECTED);
     else:
     $cat_id=rex_config::get(CONTROL,CAT_FORBIDDEN);
     endif;
   if(!empty($cat_id)):
     #
     # --- article in protected / forbidden area?
     $rc=2;
     if($art->getId()==$cat_id) $rc=3;
     $arr=explode('|',$art->getValue('path'));
     for($i=1;$i<count($arr)-1;$i=$i+1)
        if($arr[$i]==$cat_id) $rc=3;
     #
     # --- article in protected / forbidden area, is the visitor authenticated?
     if($rc==3):
       if($kont<=1):
         $auth=self::user_authenticated();
         if(empty($auth['redaxo']) and empty($auth['session'])):
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
public static function media2protect($file) {
   #   determine if access to a media file is to be denied,
   #   return value:
   #      TRUE,  if the media file belongs to the protected media category
   #             or to one of its subcategories
   #      FALSE, otherwise, or no protected category is configurated
   #   $file              given media file (relative file name)
   #
   $medcat_protected_id=rex_config::get(CONTROL,MEDCAT_PROTECTED);
   if(empty($medcat_protected_id)) return FALSE;
   $media=rex_media::get($file);
   #
   # --- file does not exist
   if($media==NULL) return FALSE;
   #
   # --- determine the top parent media category
   $cat=$media->getCategory();
   if(!empty($cat->getPathAsArray()[0])):
     $catid=$cat->getPathAsArray()[0];
     else:
     $catid=$cat->getId();
     endif;
   #
   # --- top parent media category = protected media category?
   if($catid==$medcat_protected_id) return TRUE;
   return FALSE;
   }
public static function print_file($file,$media_type) {
   #   displaying a media file, if no access is allowed a general error
   #   file ('protected.gif') is displayed instead
   #   $file              given media file (relative file name)
   #   $media_type        given media type
   #   used functions:
   #      self::user_authenticated()
   #      self::media2protect($file)
   #      rex_media_manager::sendMedia()
   #      rex_response::sendFile($file,$contentType,$contentDisposition)
   #
   ### $medfile=rex_path::media($file);  // wrong in case of media type 'mediapath'
   $counter=rex_media_manager::deleteCache($file,$media_type);
   @$manager=rex_media_manager::create($media_type,$file);
   $media=$manager->getMedia();
   $medfile=$media->getMediaPath();
   if(empty($file) or !file_exists($medfile)):
     $errfile=rex_path::addon('media_manager', 'media/warning.jpg');
     $managed_media=new rex_managed_media($errfile);
     $manager=new rex_media_manager($managed_media);
     $manager->sendMedia();
     endif;
   #
   $auth=self::user_authenticated();
   if(self::media2protect($file) and
      empty($auth['redaxo']) and empty($auth['session'])):
     #     error file displayed
     $errfile=rex_path::addonAssets(CONTROL,'protected.gif');
     $managed_media=new rex_managed_media($errfile);
     $manager=new rex_media_manager($managed_media);
     $manager->sendMedia();
     else:
     $mtype=mime_content_type($medfile);
     if(substr($mtype,0,5)=='image' or $mtype=='text/plain' or
        $mtype=='application/pdf' or $mtype=='directory'):
       #     images and pdf displayed by sendMedia()
       if(!empty($media_type)):
         $counter=rex_media_manager::deleteCache($file,$media_type);
         $manager=rex_media_manager::create($media_type,$file);
         else:
         $managed_media=new rex_managed_media($medfile);
         $manager=new rex_media_manager($managed_media);
         endif;
       $manager->sendMedia();
       else:
       #     files downloaded by sendFile(), accounting large files
       rex_response::sendFile($medfile,$mtype,'attachment');
       endif;
     endif;
   }
public static function login_page() {
   #   Displaying a login page for a visitor to get authenticated
   #   used functions:
   #      self::member_session('name');
   #      self::member_session('pwd');
   #      self::user_authenticated();
   #      self::member_session('set');
   #
   # --- 1 page source for 2 languages
   $clang_id=rex_clang::getCurrentId();
   #
   # --- Backend
   if(rex::isBackend()):
     if($clang_id==1):
       echo '<p>Anzeige einer Login-Seite zur Authentifizierung als Mitglieds-Benutzer</p>';
       else:
       echo '<p>Displaying a sign in page for authentication as member user</p>';
       endif;
     return;
     endif;
   #
   # --- Frontend
   $member=self::member_session('name');
   $mempwd=self::member_session('pwd');
   #
   # --- Language constants
   if($clang_id==1):
     $st_conf ='Bitte zunächst den Mitglieds-Benutzer nebst Passwort konfigurieren!';
     $st_in_up='Bitte Benutzername und Passwort eingeben';
     $st_wr_u ='+++ falscher Benutzername';
     $st_in_p ='Bitte Passwort eingeben';
     $st_wr_p ='+++ falsches Passwort';
     $st_user ='Benutzername';
     $st_pwd  ='Passwort';
     $st_butt ='anmelden';
     $st_butt2='abmelden';
     $st_val2 ='abmelden';
     $st_memb ='Benutzer';
     $st_auth ='erfolgreich eingeloggt';
     else:
     $st_conf ='Configure the member user including password at first, please!';
     $st_in_up='Insert user name and password, please';
     $st_wr_u ='+++ wrong user name';
     $st_in_p ='Insert password, please';
     $st_wr_p ='+++ wrong password';
     $st_user ='User name';
     $st_pwd  ='Password';
     $st_butt ='sign in';
     $st_butt2='sign off';
     $st_val2 ='signoff';
     $st_memb ='User';
     $st_auth ='authenticated successfully';
     endif;
   if(empty($member) or empty($mempwd))
     echo '<p class="access_control_error">'.$st_conf.'</p>';
   #
   # --- get login name and password ...
   $login ='';
   $passwd='';
   if(!empty($_POST['login']))  $login =$_POST['login'];
   if(!empty($_POST['passwd'])) $passwd=$_POST['passwd'];
   #
   # --- ... or sign off (delete session variable)
   if(!empty($_POST['action'])) self::member_session('end');
   #
   # --- analysing the input values
   $error='';
   if($passwd!=$mempwd) $error=$st_wr_p;
   if(empty($passwd))   $error=$st_in_p;
   if($login!=$member)  $error=$st_wr_u;
   if(empty($login))    $error=$st_in_up;
   #
   # --- display the form
   echo '
<div class="access_control_frame">
<form method="post">
<table>';
   if(!empty($error)):
     # --- sign in form
     echo '
    <tr><td>'.$st_user.': &nbsp;</td>
        <td><input type="text" name="login" value="'.$login.'" class="access_control_input" /></td></tr>
    <tr><td>'.$st_pwd.':</td>
        <td><input type="password" name="passwd" value="'.$passwd.'" class="access_control_input" /></td></tr>
    <tr><td colspan=2">
            <p class="access_control_error">'.$error.'</p></td>
    <tr><td></td>
        <td><button type="submit" name="action" value="">
            '.$st_butt.'</button></td></tr>';
     else:
     # --- set session variable
     self::member_session('set');
     # --- sign off form
     $success=$st_memb.' \''.$member.'\' '.$st_auth;
     echo '
    <tr><td><input type="hidden" name="login" value="" />
            <input type="hidden" name="passwd" value="" /></td>
        <td><p class="access_control_success">'.$success.'</p></td></tr>
    <tr><td></td>
        <td><button type="submit" name="action" value="'.$st_val2.'">
            '.$st_butt2.'</button></td></tr>';
     endif; 
   echo '
</table>
</form>
</div>
';
   }
public static function configuration() {
   #   read and save the configuration parameters
   #
   # --- read in the configuration parameters
   $conf_forb_id   =rex_config::get(CONTROL,CAT_FORBIDDEN);
   $conf_prot_id   =rex_config::get(CONTROL,CAT_PROTECTED);
   $conf_protmed_id=rex_config::get(CONTROL,MEDCAT_PROTECTED);
   if($conf_protmed_id=='0') $conf_protmed_id='';
   $conf_memb_login=rex_config::get(CONTROL,MEMBER_LOGIN);
   $conf_memb_pwd  =rex_config::get(CONTROL,MEMBER_PASSWORD);
   #
   if(empty($_POST['save'])):
     #
     # --- fill the configuration parameters into the form
     $forb_id   =$conf_forb_id;
     $prot_id   =$conf_prot_id;
     $protmed_id=$conf_protmed_id;
     $memb_login=$conf_memb_login;
     $memb_pwd  =$conf_memb_pwd;
     else:
     #
     # --- read the inserted parameters
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
     # --- save the new configuration parameters (after submit)
     rex_config::set(CONTROL,CAT_FORBIDDEN,   intval($forb_id));
     rex_config::set(CONTROL,CAT_PROTECTED,   intval($prot_id));
     rex_config::set(CONTROL,MEDCAT_PROTECTED,intval($protmed_id));
     rex_config::set(CONTROL,MEMBER_LOGIN,    $memb_login);
     rex_config::set(CONTROL,MEMBER_PASSWORD, $memb_pwd);
     endif;
   #
   # --- define input buttons and fields
   $input_forb_id   =rex_var_link::getWidget (1,'forb_id',   $forb_id);
   $input_prot_id   =rex_var_link::getWidget (2,'prot_id',   $prot_id);
   $input_protmed_id=rex_var_media::getWidget(1,'protmed_id',$protmed_id);
   #     the buttons HTML codes contain:
   # <input type="hidden" name="forb_id" id="REX_LINK_1" value="***" />
   # <input type="hidden" name="prot_id" id="REX_LINK_2" value="***" />
   # <input class="form-control" type="text" name="protmed_id" value="***" id="REX_MEDIA_1" readonly />
   $input_memb_login='<input class="form-control" type="text" name="memb_login" value="'.$memb_login.'" />';
   $input_memb_pwd  ='<input class="form-control" type="password" name="memb_pwd" value="'.$memb_pwd.'" />';
   #
   # --- protected categories and media categories
   echo '<div>'.rex_i18n::msg("access_control_settings_first").'</div>
<br/>
<form method="post">
<table class="access_control_table">
    <tr><td colspan="3">
            '.rex_i18n::msg("access_control_settings_par1").'</td></tr>
    <tr><td class="'.CLASS_NOWRAP.'"></td>
        <td class="'.CLASS_INDENT.'"><small>
            '.rex_i18n::msg("access_control_settings_col12").'</small></td>
        <td class="'.CLASS_INDENT.'"><small>
            '.rex_i18n::msg("access_control_settings_col13").'</small></td></tr>
    <tr><td class="'.CLASS_NOWRAP.'">
            '.rex_i18n::msg("access_control_settings_col21").':</td>
        <td class="'.CLASS_INDENT.'">
            '.$input_prot_id.'</td>
        <td class="'.CLASS_INDENT.'">
            '.rex_i18n::msg("access_control_settings_col23").'</td></tr>
    <tr><td class="'.CLASS_NOWRAP.'">
            '.rex_i18n::msg("access_control_settings_col31").':</td>
        <td class="'.CLASS_INDENT.'">
            '.$input_protmed_id.'</td>
        <td class="'.CLASS_INDENT.'">
            '.rex_i18n::msg("access_control_settings_col33").'</td></tr>
    <tr><td class="'.CLASS_NOWRAP.'">
            '.rex_i18n::msg("access_control_settings_col41").' (*):</td>
        <td class="'.CLASS_INDENT.'">
            '.$input_forb_id.'</td>
        <td class="'.CLASS_INDENT.'">
            '.rex_i18n::msg("access_control_settings_col43").'</td></tr>
    <tr><td></td>
        <td colspan="2" class="'.CLASS_INDENT.'">
            (*)<small> &nbsp; '.
              rex_i18n::msg("access_control_settings_col52").'</small></td></tr>';
   #
   # --- access data for the member user
   echo '
    <tr><td colspan="3"><br/>
            '.rex_i18n::msg("access_control_settings_par2").'</td></tr>
    <tr><td class="'.CLASS_NOWRAP.'">
            '.rex_i18n::msg("access_control_settings_user").':</td>
        <td class="'.CLASS_INDENT.'">
            '.$input_memb_login.'</td>
        <td class="'.CLASS_INDENT.'"></td></tr>
    <tr><td class="'.CLASS_NOWRAP.'">
            '.rex_i18n::msg("access_control_settings_pwd").':</td>
        <td class="'.CLASS_INDENT.'">
            '.$input_memb_pwd.'</td>
        <td class="'.CLASS_INDENT.'"></td></tr>
    <tr><td></td>
        <td class="'.CLASS_INDENT.'" colspan="2"><br/>
            <button class="btn btn-save" type="submit" name="save" value="save"
                    title="'.rex_i18n::msg("access_control_settings_title").'">'.
            rex_i18n::msg("access_control_settings_text").'</button></td></tr>
</table>
</form>';
   }
}
?>