<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Oktober 2019
 */
define('CONTROL',     $this->getPackageId());  // Package-Id
#     description of inactive Redaxo users controlling protected/forbidden categories
define('PROTECTOR',  'Protector');   // guarding user
define('GUARDIAN',   'Guardian');    // guarding user for the forbidden area
#
class access_control {
#
public static function protected_categories($role,$struc_media) {
   #   Returning the categories or media categories defined in a
   #   Redaxo single user role.
   #   $role              given role ID
   #   $struc_media       ='structure'/'media'
   #
   if(empty($role)) return;
   $sql=rex_sql::factory();
   $query='SELECT * FROM rex_user_role WHERE id='.$role;
   $rls=$sql->getArray($query);
   $rl=$rls[0];
   $perms=$rl['perms'];
   $perm=explode(',',$perms);
   for($j=0;$j<count($perm);$j=$j+1):
      if(strpos($perm[$j],$struc_media)>0):
        $pe=$perm[$j];
        if(empty($pe)) return;
        $pe=str_replace('"','',$pe);
        $brr=explode(':',$pe);
        $catid=str_replace('|',',',$brr[1]);
        $catid=str_replace('null','',$catid);
        $catid=str_replace(',,',',',$catid);
        if(!empty($catid) and strtolower($catid)!='null') $catid=substr($catid,1,strlen($catid)-2);
        return $catid;
        endif;
      endfor;
   }
public static function protected_categories_user($roles,$struc_media) {
   #   Returning the categories or media categories defined in a Redaxo user role
   #   as comma separated string.
   #   $roles             given comma separated string of role ids
   #   $struc_media       ='structure'/'media'
   #   used functions:
   #      self::protected_categories($roles[$k],$struc_media)
   #
   if(empty($roles)) return;
   $role=explode(',',$roles);
   $ciduni=array();   // array of unique category IDs
   $catid='';
   for($k=0;$k<count($role);$k=$k+1):
      $cidstr=self::protected_categories($role[$k],$struc_media);
      $cid=explode(',',$cidstr);
      for($i=0;$i<count($cid);$i=$i+1):
         $ccid=$cid[$i];
         $ciduni[$k]=$ccid;
         if(!empty($ccid)):
           for($j=0;$j<$k;$j=$j+1):
              if($ccid==$ciduni[$j]):
                $ccid='';   // category ID already included
                break;
                endif;
              endfor;
           if(!empty($ccid)) $catid=$catid.','.$ccid;
           endif;
         if(substr($catid,0,1)==',') $catid=substr($catid,1);
         endfor;
      endfor;
    return $catid;
   }
public static function guardian_users() {
   #   Returns all guardian users in a numbered array (numbering from 1).
   #   Each array element is an associative array containing
   #      ['id']          Redaxo user ID (column 'id')
   #      ['login']       Redaxo user login (column 'login')
   #      ['password']    Redaxo user password (column 'password')
   #      ['description'] Redaxo user description (column 'description')
   #      ['catid']       Ids of categories the user has access to
   #                      (comma separated string)
   #      ['medcatid']    Ids of media categories the user has access to
   #                      (comma separated string)
   #   used functions:
   #      self::protected_categories_user($roles,$struc_media)
   #   In this AddOn the access to protected categories and/or media categories
   #   is controlled by Redaxo users ('guardian' users) by verifying user login
   #   and password. The appropriate users must be defined with fixed description
   #   'PROTECTOR' (or 'GUARDIAN', access restricted to Redaxo site administrator
   #   only) and should be inactive in order to prevent password changing via
   #   backend sign-in. They must be assigned a role defining the access to
   #   categories and/or media categories. Theese categories and media categories
   #   are protected by authentication of the related user.
   #
   $sql=rex_sql::factory();
   $where='status=0 AND (description=\''.PROTECTOR.'\' OR description=\''.GUARDIAN.'\')';
   $query='SELECT * FROM rex_user WHERE '.$where;
   $users=$sql->getArray($query);
   $user=array();
   for($i=0;$i<count($users);$i=$i+1):
      $m=$i+1;
      #     user id, login and password
      $user[$m]['id']         =$users[$i]['id'];
      $user[$m]['login']      =$users[$i]['login'];
      $user[$m]['password']   =$users[$i]['password'];
      $user[$m]['description']=$users[$i]['description'];
      if($users[$i]['description']==GUARDIAN) $user[$m]['password']='';
      $roles=$users[$i]['role'];
      #     roles/categories
      $user[$m]['catid']=self::protected_categories_user($roles,'structure');
      #     roles/media categories
      $user[$m]['medcatid']=self::protected_categories_user($roles,'media');
      endfor;
   return $user;
   }
public static function guarded_category($art_id) {
   #   Returns the ID of a protected or forbidden category if a given article
   #   is in the path of that category.
   #   $art_id            ID of the given article
   #   used functions:
   #      self::guardian_users()
   #
   $art=rex_article::get($art_id);
   $users=self::guardian_users();
   for($i=1;$i<=count($users);$i=$i+1):
      $user=$users[$i];
      $catid=$user['catid'];
      $arr=explode(',',$catid);
      for($k=0;$k<count($arr);$k=$k+1):
         $cat_id=$arr[$k];
         if($art_id==$cat_id) return $cat_id;
         $brr=explode('|',$art->getValue('path'));
         for($j=0;$j<count($brr);$j=$j+1)
            if($brr[$j]==$cat_id) return $cat_id;
         endfor;
      endfor;
   }
public static function article_guarding_users($art_id) {
   #   Returns the users controlling the access to a category of a given
   #   article (guardian users) in a numbered array. Each user is represented
   #   as an associative array containing
   #      ['id']          his ID (column 'id')
   #      ['login']       his login (column 'login')
   #      ['description'] his description (column 'description')
   #      ['catid']       the ID of the protected category containing the article
   #   $art_id            ID of the given article
   #   used functions:
   #      self::guarded_category($art_id)
   #      self::guardian_users()
   #
   # --- article not located in any protected or forbidden category
   $cat_id=self::guarded_category($art_id);
   if(empty($cat_id)) return;
   #
   # --- article located in one of the protected or forbidden categories
   $users=self::guardian_users();
   $brr=array();
   $m=0;
   for($i=1;$i<=count($users);$i=$i+1):
      $catid=$users[$i]['catid'];
      $arr=explode(',',$catid);
      for($k=0;$k<count($arr);$k=$k+1)
         if($arr[$k]==$cat_id):
           $m=$m+1;
           $brr[$m]=array('id'=>$users[$i]['id'],'login'=>$users[$i]['login'],
                          'description'=>$users[$i]['description'],'catid'=>$cat_id);
           endif;
      endfor;
   return $brr;
   }
public static function get_rex_editor() {
   #   Returns the Redaxo editor in an assocative array if he has authenticated:
   #      ['id']          userID(column 'id')
   #      ['login']       user login (column 'login')
   #      ['role']        user role (column 'role', comma separated string)
   #      ['catid']       comma separated string of category Ids
   #                      being controlled by the user
   #      ['medcatid']    comma separated string of media category Ids
   #                      being controlled by the user
   #   used functions:
   #      self::protected_categories_user($roles,$struc_media)
   #
   if(rex_backend_login::createUser()):
     $uid=rex::getUser()->getValue('id');
     if($uid>0):
       $login=rex::getUser()->getValue('login');
       $sql=rex_sql::factory();
       $query='SELECT * FROM rex_user WHERE id='.$uid;
       $users=$sql->getArray($query);
       $roles=$users[0]['role'];
       $catid=self::protected_categories_user($roles,'structure');
       $medcatid=self::protected_categories_user($roles,'media');
       return array('id'=>$uid,'login'=>$login,'roles'=>$roles,'catid'=>$catid,'medcatid'=>$medcatid);
       endif;
     endif;
   return array('id'=>'','login'=>'','roles'=>'','catid'=>'','medcatid'=>'');
   }
public static function session_variable($login='') {
   #   Setting or getting the session variable.
   #   !empty($login):       authenticate (sign in) the guardian user $login,
   #                         i.e. set the appropriate session variable,
   #                         return ''.
   #   empty($login):        return login name of a guardian user
   #                         (= value of the session variable, if he is authenticated)
   #                         return value '' (none of the guardian user is authenticated).
   #
   $session='';
   if(!empty($login)):
     # --- set session variable
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     $_SESSION[CONTROL]=$login;
     else:
     # --- get session variable
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     if(!empty($_SESSION[CONTROL])) $session=$_SESSION[CONTROL];
     endif;
   return $session;
   }
public static function session_end($login) {
   #   End session (sign off) for a guardian user
   #   $login             the guardian user login name
   #
   if(!empty($login)):
     $session='';
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     if(!empty($_SESSION[CONTROL])) $session=$_SESSION[CONTROL];
     if($login==$session) $_SESSION[CONTROL]='';
     endif;
   }
public static function access_allowed($art_id) {
   #   Returns the following data concerning an article in an associative array:
   #      ['id']    =ID of the associated guardian user (column 'id')
   #                =0  if the article is not located in a protected area 
   #      ['allow']   whether the access to an article
   #                =0  is allowed
   #                =1  is denied (access to the forbidden category)
   #                =2  is denied (access to a protected category)
   #   The guardian user may be:
   #      -   the site administrator (authenticated/not authenticated in the backend)
   #      -   a Redaxo editor for a category containing the given article
   #          (authenticated/not authenticated in the backend)
   #      -   an inactive user controlling the access to a category containing the
   #          given article (authenticated/not authenticated)
   #   $art_id           ID of the given article
   #   used functions:
   #      self::get_rex_editor()
   #      self::article_guarding_users($art_id)
   #      self::session_variable()
   #
   $rexed=self::get_rex_editor();
   $us   =self::article_guarding_users($art_id);
   #
   # --- article not located in a protected or forbidden category
   if(count($us)<=0) return array('id'=>0,'allow'=>0);
   #
   # --- site administrator, signed on
   $redid=$rexed['id'];
   if($redid==1) return array('id'=>$redid,'allow'=>0);
   #
   # --- forbidden area, access denied
   for($i=1;$i<=count($us);$i=$i+1)
      if($us[$i]['description']==GUARDIAN) return array('id'=>$us[$i]['id'],'allow'=>1);
   #
   # --- protected area, Redaxo editor signed on
   if($redid>=2):
     $cat_id=$us[1]['catid'];
     $catid =$rexed['catid'];
     $arr=explode(',',$catid);
     for($k=0;$k<count($arr);$k=$k+1)
        if($arr[$k]==$cat_id) return array('id'=>$redid,'allow'=>0);
     endif;
   #
   # --- protected category, authenticated
   $session=self::session_variable();
   $uid=0;
   for($i=1;$i<=count($us);$i=$i+1)
      if($us[$i]['login']==$session):
        $uid=$us[$i]['id'];
        break;
        endif;
   if($uid>0) return array('id'=>$uid,'allow'=>0);
   #
   # --- protected category, not authenticated
   for($i=1;$i<=count($us);$i=$i+1)
      if($us[$i]['description']!=GUARDIAN)
        return array('id'=>$us[$i]['id'],'allow'=>2);
   }
public static function protected_or_forbidden() {
   #   Determines if access to the actual article needs to be denied
   #   returning the following value:
   #      =0      access is allowed because the article is public or the
   #              visitor has authenticated
   #      =1      access denied, the article is located in the forbidden category
   #              and the visitor has not authenticated as site adminstrator
   #      >1      access denied, the article is located in one of the protected
   #              categories and the visitor has to authenticate as the user
   #              controlling the access to the category (value is the ID of
   #              the guarding user of the actual article)
   #   used functions:
   #      self::protected_or_forbidden_intern($art_id)
   #
   #   exemplary section of the page template, show an error message or present
   #   a sign-in form instead of the article content
   #      ...
   #      $uid=access_control::protected_or_forbidden();
   #      if($uid>0):
   #        if($uid==1):
   #          #   forbidden
   #          else:
   #          #   protected
   #          echo '... <a href="...../login_page.html?uid=$uid">Sign in</a> ...';
   #          endif;
   #        else:
   #        echo $this->getArticle(); // show the article content
   #        endif;
   #      endif;
   #      ...
   #
   $art_id=rex_article::getCurrentId();
   return self::protected_or_forbidden_intern($art_id);
   }
public static function protected_or_forbidden_intern($art_id) {
   #   Return value: see protected_or_forbidden()
   #   $art_id           ID of the given article
   #   used functions:
   #      self::access_allowed($art_id)
   #
   $brr=self::access_allowed($art_id);
   $uid=$brr['id'];
   if($brr['allow']==1) $uid=1;
   if($brr['allow']==0) $uid=0;
   return $uid;
   }
public static function get_guardian_user($uid) {
   #   Returns the user login (column 'login') and the encrypted password
   #   of a given guardian user in an associative array.
   #   $uid               given userID (column 'id')
   #   used functions:
   #      self::guardian_users()
   #
   $users=self::guardian_users();
   for($i=1;$i<=count($users);$i=$i+1)
      if($users[$i]['id']==$uid):
        $login   =$users[$i]['login'];
        $password=$users[$i]['password'];
        $descript=$users[$i]['description'];
        return array('password'=>$password,'login'=>$login,'description'=>$descript);
        endif;
   }
public static function get_locale() {
   #   Get the locale of the current article ('de_de' or 'en_gb')
   #
   $loc_all=rex_i18n::getLocales();
   $code   =rex_clang::get(rex_clang::getCurrentId())->getCode();
   $locale='';
   for($i=0;$i<count($loc_all);$i=$i+1):
      $loc=$loc_all[$i];
      $brr=explode('_',$loc);
      if($code==$brr[0]):
        $locale=$loc;
        break;
        endif;
      endfor;
   return $locale;
   }
public static function login_page() {
   #   Displaying an article for authentication of a guardian user (sign-in page).
   #   The user ID of that guardian user (column 'id') must be entered as an
   #   URL parameter 'uid=$uid' of that article.
   #   Return value: user ID of that guardian user
   #                 or 0 (missing URL parameter or wrong user ID)
   #   used functions:
   #      self::get_guardian_user($uid)
   #      self::get_locale()
   #      self::session_variable($login)
   #      self::session_end($login)
   #
   # --- get URL parameter
   $uid='';
   if(!empty($_GET['uid'])) $uid=$_GET['uid'];
   if(empty($uid)) return 0;
   #
   # --- get the user's login and (encrypted) password
   $arr=self::get_guardian_user($uid);
   $lognam  =$arr['login'];
   $password=$arr['password'];
   if(empty($lognam)) return 0;
   #
   # --- get input login name and password ...
   $login ='';
   $passwd='';
   if(!empty($_POST['login']))  $login =$_POST['login'];
   if(!empty($_POST['passwd'])) $passwd=$_POST['passwd'];
   #
   # --- ... or sign off (delete session variable)
   if(!empty($_POST['action'])):
     self::session_end($lognam);
     endif;
   #
   # --- setLocale
   rex_i18n::setLocale(self::get_locale());
   #
   # --- analysing the input values
   $error='';
   $ok=rex_login::passwordVerify($passwd,$password);
   if(!$ok)              $error=rex_i18n::msg('access_control_login_wrong_pwd');
   if(empty($passwd))    $error=rex_i18n::msg('access_control_login_in_pwd');
   if($login!=$lognam)   $error=rex_i18n::msg('access_control_login_wrong_username');
   if(empty($login))     $error=rex_i18n::msg('access_control_login_in_username_pwd');
   #
   # --- display the form
   echo '
<div class="access_control_frame">
<form method="post">
<table>';
   if(!empty($error)):
     #     sign in form
     echo '
    <tr><td>'.rex_i18n::msg('access_control_login_username').': &nbsp;</td>
        <td><input type="text" name="login" value="'.$login.'" class="access_control_input" /></td></tr>
    <tr><td>'.rex_i18n::msg('access_control_login_pwd').':</td>
        <td><input type="password" name="passwd" value="'.$passwd.'" class="access_control_input" /></td></tr>
    <tr><td colspan=2">
            <p class="access_control_error">'.$error.'</p></td>
    <tr><td></td>
        <td><button type="submit" name="action" value="">
            '.rex_i18n::msg('access_control_login_button_in').'</button></td></tr>';
     else:
     #     set session variable
     self::session_variable($lognam);
     #     sign off form
     $success=rex_i18n::msg('access_control_login_user').' \''.$lognam.'\' '.
              rex_i18n::msg('access_control_login_authenticated');
     echo '
    <tr><td><input type="hidden" name="login" value="" />
            <input type="hidden" name="passwd" value="" /></td>
        <td><p class="access_control_success">'.$success.'</p></td></tr>
    <tr><td></td>
        <td><button type="submit" name="action" value="'.rex_i18n::msg('access_control_login_val_off').'">
            '.rex_i18n::msg('access_control_login_button_off').'</button></td></tr>';
     endif; 
   echo '
</table>
</form>
</div>
';
   return $uid;
   }
public static function top_parent_media_category($file) {
   #   Returning the top parent_media_category of a media file.
   #   $file              given media file (relative file name)
   #
   $media=rex_media::get($file);
   #
   # --- file does not exist
   if($media==NULL) return 0;
   #
   # --- determine the top parent media category
   $medcat=$media->getCategory();
   $medcatid=0;
   if(!empty($medcat->getPathAsArray()[0])):
     $medcatid=$medcat->getPathAsArray()[0];
     else:
     $medcatid=$medcat->getId();
     endif;
   return $medcatid;
   }
public static function media2protect($file) {
   #   Determines whether access to a media file is controlled by guardian users.
   #   The guardian users are return in a numbered array (numbering from 1).
   #   $file              given media file (relative file name)
   #   used functions:
   #      self::top_parent_media_category($file)
   #      self::guardian_users()
   #
   # --- is the top parent media category protected?
   $medcatid=self::top_parent_media_category($file);
   if($medcatid<=0) return array();
   $gusers=self::guardian_users();
   $uid=array();
   $m=0;
   for($i=1;$i<=count($gusers);$i=$i+1):
      $med=explode(',',$gusers[$i]['medcatid']);
      for($k=0;$k<count($med);$k=$k+1)
         if($med[$k]==$medcatid):
           $m=$m+1;
           $uid[$m]=$gusers[$i]['id'];
           endif;
      endfor;
   return $uid;
   }
public static function rex_editor_authenticated() {
   #   check if the visitor is authenticated as Redaxo editor.
   #   return value is an associative array containing:
   #      $auth['redaxo']  = Redaxo editor's userID if he has signed on or
   #                         '' if the Redaxo editor has not signed on
   #      $auth['medcatid']= comma separated string of media category Ids
   #                         the rex editor controls the access to
   #   used functions:
   #      self::get_rex_editor()
   #
   $auth['redaxo']='';
   $arr=self::get_rex_editor();
   if($arr['id']>=1) $auth['redaxo']=$arr['id'];
   $auth['medcatid']='';
   if($arr['id']>1) $auth['medcatid']=$arr['medcatid'];
   return $auth;
   }
public static function user_authenticated($uid) {
   #   Returning the user login of a guardian user if the visitor has
   #   authenticated as this user.
   #   $uid              ID of the given guardian user
   #   used functions:
   #      self::session_variable($login)
   #      self::get_guardian_user($id)
   #
   if($uid<=0) return;
   $session=self::session_variable();
   if($session==self::get_guardian_user($uid)['login']) return $session;
   }
public static function print_file($file,$media_type='') {
   #   Displaying a media file (or an error file, if no access is allowed).
   #   $file              given media file (relative file name)
   #   $media_type        given media type
   #   used functions:
   #      self::media2protect($file)
   #      self::rex_editor_authenticated()
   #      self::top_parent_media_category($file)
   #      self::user_authenticated($id)
   #      rex_media_manager::sendMedia()
   #      rex_response::sendFile($file,$contentType,$contentDisposition)
   #
   # --- rex_media_manager object and absolute path to the media file
   if(empty($media_type)):
     $medfile=rex_path::media($file);
     $managed_media=new rex_managed_media($medfile);
     $manager=new rex_media_manager($managed_media);
     else:
     $counter=rex_media_manager::deleteCache($file,$media_type);
     $manager=@rex_media_manager::create($media_type,$file);
     ### create()... Notice: getimagesize(): Read error... in case of 'mediapath' effect
     $media=$manager->getMedia();
     $medfile=$media->getMediaPath();
     $medfile=dirname($medfile.'zzz').'/'.$file;  // in case of $medfile '.../media/'
     $effects=$manager->effectsFromType($media_type);
     for($i=0;$i<count($effects);$i=$i+1)
        if($effects[$i]['effect']=='mediapath'):
          $managed_media=new rex_managed_media($medfile);
          $manager=new rex_media_manager($managed_media);
          break;
          endif;
     endif;
   $mtype=mime_content_type($medfile);
   #
   # --- output error file (file not found)
   if(empty($file) or !file_exists($medfile) or $mtype=='directory'):
     $errfile=rex_path::addon('media_manager', 'media/warning.jpg');
     $managed_media=new rex_managed_media($errfile);
     $manager=new rex_media_manager($managed_media);
     $manager->sendMedia();
     endif;
   #
   # --- media file protected?
   $protected=FALSE;
   $protid=self::media2protect($file);
   for($i=1;$i<=count($protid);$i=$i+1)
      if($protid[$i]>0):
        $protected=TRUE;
        break;
        endif;
   #
   # --- access allowed?
   $allowed=TRUE;
   if($protected):
     #     site administrator or Redaxo editor has signed on?
     $auth=self::rex_editor_authenticated();
     $son=FALSE;
     if($auth['redaxo']==1) $son=TRUE;   // site administrator
     if($auth['redaxo']>=2):
       $medcatid=self::top_parent_media_category($file);
       $arr=explode(',',$auth['medcatid']);
       for($k=0;$k<count($arr);$k=$k+1)
          if($arr[$k]==$medcatid) $son=TRUE;   // Redaxo editor
       endif;
     #     guardian user has signed in?
     for($i=1;$i<=count($protid);$i=$i+1)
        if(!empty(self::user_authenticated($protid[$i]))) $son=TRUE;   // guardian user
     if(!$son) $allowed=FALSE;
     endif;
   #
   # --- output file
   if($allowed):
     if(substr($mtype,0,5)=='image' or $mtype=='application/pdf'):
       $manager->sendMedia();  // images and pdf documents
       else:
       if($protected) rex_response::setHeader('Last-Modified','Wed, 20 Oct 2100 07:28:00 GMT1');
       rex_response::sendFile($medfile,$mtype,'attachment');  // accounting large files
       endif;
     else:
   #
   # --- output error file (file protected)
     $errfile=rex_path::addonAssets(CONTROL,'protected.gif');
     $managed_media=new rex_managed_media($errfile);
     $manager=new rex_media_manager($managed_media);
     $manager->sendMedia();
     endif;
   }
}
?>