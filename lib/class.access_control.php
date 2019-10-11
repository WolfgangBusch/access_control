<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Oktober 2019
 */
define('CONTROL',     $this->getPackageId());  // Package-ID
#     description of inactive Redaxo users controlling protected/forbidden categories
define('PROTECTOR',  'Protector');   // guardian user
define('GUARDIAN',   'Guardian');    // guardian user for the forbidden area
define('PERM_STRUC', 'structure');   // rex_user_role complex_perm 'structure'
define('PERM_MEDIA', 'media');       // rex_user_role complex_perm 'media'
#
class access_control {
# ----- functions -----------------------------------------------------
#   protected_or_forbidden()
#      protected_or_forbidden_intern($art_id)
#         access_allowed($art_id)
#            get_rex_editor()
#               rex_user_login($uid)
#               protected_categories($roles,$permtype)
#            article_guardian_users($art_id)
#               guardian_users()
#                  rex_guardian_users($cond)
#                  protected_categories_user($roles,$permtype)
#                     protected_categories($role,$permtype)
#               guarded_category($art_id,$gusers)
#            session_get()
#   login_page()
#      get_locale()
#      rex_user_login($uid)
#      session_set($login)
#      session_end($login)
#   print_file($file,$media_type='')
#      top_parent_media_category($file)
#      media_guardian_user($file,$tpmcatid)
#         rex_guardian_users($cond)
#         protected_categories_user($roles,$permtype)
#            protected_categories($roles,$permtype)
#      rex_user_login($uid)
#      get_rex_editor()
#         protected_categories($roles,$permtype)
#      session_get()
# ----- basic ---------------------------------------------------------
public static function rex_user_login($uid) {
   #   Returns one user from table rex_user.
   #   $uid               ID of the given user
   #
   if($uid<=0) return array();
   #
   $sql=rex_sql::factory();
   $users=$sql->getArray('SELECT * FROM rex_user WHERE id='.$uid);
   if(count($users)>0) return $users[0];
   return array();
   }
public static function rex_guardian_users($cond=0) {
   #   Returns all guardian users from table rex_user.
   #   $cond              =0:   all guardian users excluding GUARDIAN
   #                      else: all guarding users including GUARDIAN
   #
   $sql=rex_sql::factory();
   $where='WHERE status=0 AND ';
   if(abs($cond)>0):
      $where=$where.'(description=\''.PROTECTOR.'\' OR description=\''.GUARDIAN.'\')';
      else:
      $where=$where.'description=\''.PROTECTOR.'\'';
      endif;
   $users=$sql->getArray('SELECT * FROM rex_user '.$where);
   return $users;
   }
public static function get_locale() {
   #   Returns the locale of the current article ('de_de' or 'en_gb').
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
public static function session_set($login) {
   #   Authenticates (sign in) a guardian user, sets the session variable.
   #
   if(session_status()!=PHP_SESSION_ACTIVE) session_start();
   $_SESSION[CONTROL]=$login;
   }
public static function session_get() {
   #   Returns the value of the session variable. I.e. the login name of
   #   a guardian user who is authenticated.
   #
   $session='';
   if(session_status()!=PHP_SESSION_ACTIVE) session_start();
   if(!empty($_SESSION[CONTROL])) $session=$_SESSION[CONTROL];
   return $session;
   }
public static function session_end($login) {
   #   Ends session, sign off a guardian user.
   #   $login             the guardian user login name
   #
   if(!empty($login)):
     $session='';
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     if(!empty($_SESSION[CONTROL])) $session=$_SESSION[CONTROL];
     if($login==$session) $_SESSION[CONTROL]='';
     endif;
   }
public static function protected_categories($role,$permtype) {
   #   Returns the categories or media categories defined in a
   #   Redaxo single user role.
   #   $role              given role ID
   #   $permtype          =PERM_STRUC/PERM_MEDIA
   #
   if(empty($role)) return;
   #
   $sql=rex_sql::factory();
   $query='SELECT * FROM rex_user_role WHERE id='.$role;
   $rls=$sql->getArray($query);
   $rl=$rls[0];
   $perms=$rl['perms'];
   $arr=json_decode($perms,TRUE);
   $per='';
   for($i=0;$i<count($arr);$i=$i+1):
      $val=trim($arr[$permtype]);
      if(!empty($val)):
        $per=$val;
        $per=substr($per,1,strlen($per)-2);
        return str_replace('|',',',$per);
        break;
        endif;
      endfor;
   }
public static function protected_categories_user($roles,$permtype) {
   #   Returns the IDs of categories or media categories defined in a
   #   Redaxo user role as comma separated string.
   #   $roles             given comma separated string of role IDs
   #   $permtype          =PERM_STRUC/PERM_MEDIA
   #   used functions:
   #      self::protected_categories($role,$permtype)
   #
   if(empty($roles)) return;
   #
   # --- join the list of category IDs
   $role=explode(',',$roles);
   $catid='';
   for($k=0;$k<count($role);$k=$k+1):
      $cidstr=self::protected_categories($role[$k],$permtype);
      if(!empty($cidstr)) $catid=$catid.','.$cidstr;
      if(substr($catid,0,1)==',') $catid=substr($catid,1);
      endfor;
   #
   # --- remove duplicate IDs
   $id=explode(',',$catid);
   $ciduni=array($id[0]);   // array of unique category IDs
   $catunid='';
   $m=0;
   for($k=0;$k<count($id);$k=$k+1):
      $cid=$id[$k];
      for($j=0;$j<$m;$j=$j+1):
         if($cid==$ciduni[$j]):
           $cid='';   // category ID already included
           break;
           endif;
         endfor;
      if(!empty($cid)):
        $m=$m+1;
        $ciduni[$k]=$cid;
        $catunid=$catunid.','.$cid;
        if(substr($catunid,0,1)==',') $catunid=substr($catunid,1);
        endif;
      endfor;
   return $catunid;
   }
public static function get_rex_editor() {
   #   Returns the Redaxo editor in an assocative array if he has authenticated:
   #      ['id']          user ID (column 'id')
   #      ['login']       user login (column 'login')
   #      ['role']        user role (column 'role', comma separated string)
   #      ['catid']       comma separated string of category IDs
   #                      being controlled by the user
   #      ['medcatid']    comma separated string of media category IDs
   #                      being controlled by the user
   #   used functions:
   #      self::rex_user_login($uid)
   #      self::protected_categories($roles,$permtype)
   #
   if(rex_backend_login::createUser()):
     $uid=rex::getUser()->getValue('id');
     if($uid>0):
       $login=rex::getUser()->getValue('login');
       $user=self::rex_user_login($uid);
       $roles=$user['role'];
       $catid=self::protected_categories($roles,PERM_STRUC);
       $medcatid=self::protected_categories($roles,PERM_MEDIA);
       return array('id'=>$uid,'login'=>$login,'roles'=>$roles,'catid'=>$catid,'medcatid'=>$medcatid);
       endif;
     endif;
   return array('id'=>'','login'=>'','roles'=>'','catid'=>'','medcatid'=>'');
   }
# ----- protected_or_forbidden ----------------------------------------
public static function guardian_users() {
   #   Returns all guardian users in a numbered array (numbering from 1).
   #   Each array element is an associative array containing
   #      ['id']          Redaxo user ID (column 'id')
   #      ['login']       Redaxo user login (column 'login')
   #      ['password']    Redaxo user password (column 'password')
   #      ['description'] Redaxo user description (column 'description')
   #      ['catid']       IDs of categories the user has access to
   #                      (comma separated string)
   #   used functions:
   #      self::rex_guardian_users($cond)
   #      self::protected_categories_user($roles,$permtype)
   #
   #   general description:
   #      In this AddOn the access to protected categories and/or media
   #      categories is controlled by Redaxo users ('guardian' users) by
   #      verifying user login and password. The appropriate users must
   #      be defined with fixed description 'PROTECTOR' (or 'GUARDIAN',
   #      access restricted to Redaxo site administrator only) and should
   #      be inactive in order to prevent password changing via backend
   #      sign-in. They must be assigned a role defining the access to
   #      categories and/or media categories. These categories and media
   #      categories are protected by authentication of the related user.
   #
   $users=self::rex_guardian_users(1);
   $gusers=array();
   for($i=0;$i<count($users);$i=$i+1):
      $m=$i+1;
      #     user id, login and password
      $gusers[$m]['id']         =$users[$i]['id'];
      $gusers[$m]['login']      =$users[$i]['login'];
      $gusers[$m]['password']   =$users[$i]['password'];
      $gusers[$m]['description']=$users[$i]['description'];
      if($users[$i]['description']==GUARDIAN) $gusers[$m]['password']='';
      $roles=$users[$i]['role'];
      #     roles/categories
      $gusers[$m]['catid']=self::protected_categories_user($roles,PERM_STRUC);
      endfor;
   return $gusers;
   }
public static function guarded_category($art_id,$gusers) {
   #   Returns the ID of a protected or forbidden category if a given article
   #   is in the path of that category.
   #   $art_id            ID of the given article
   #   $gusers            array of all defined guardian users
   #
   $art=rex_article::get($art_id);
   for($i=1;$i<=count($gusers);$i=$i+1):
      $catid=$gusers[$i]['catid'];
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
public static function article_guardian_users($art_id) {
   #   Returns the users controlling the access to a category of a given
   #   article (guardian users) in a numbered array. Each user is represented
   #   as an associative array containing
   #      ['id']          his ID (column 'id')
   #      ['login']       his login (column 'login')
   #      ['description'] his description (column 'description')
   #      ['catid']       the ID of the protected category containing the article
   #   $art_id            ID of the given article
   #   used functions:
   #      self::guardian_users()
   #      self::guarded_category($art_id,$gusers)
   #
   # --- article not located in any protected or forbidden category
   $gusers=self::guardian_users();
   $cat_id=self::guarded_category($art_id,$gusers);
   if(empty($cat_id)) return;
   #
   # --- article located in one of the protected or forbidden categories
   $brr=array();
   $m=0;
   for($i=1;$i<=count($gusers);$i=$i+1):
      $catid=$gusers[$i]['catid'];
      $arr=explode(',',$catid);
      for($k=0;$k<count($arr);$k=$k+1)
         if($arr[$k]==$cat_id):
           $m=$m+1;
           $brr[$m]=array('id'=>$gusers[$i]['id'],'login'=>$gusers[$i]['login'],
                          'description'=>$gusers[$i]['description'],'catid'=>$cat_id);
           endif;
      endfor;
   return $brr;
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
   #      self::article_guardian_users($art_id)
   #      self::session_get()
   #
   $rexed=self::get_rex_editor();
   $us   =self::article_guardian_users($art_id);
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
   $session=self::session_get();
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
   #              the guardian user of the actual article)
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
# ----- login_page ----------------------------------------------------
public static function login_page() {
   #   Displaying an article for authentication of a guardian user (sign-in page).
   #   The user ID of that guardian user (column 'id') must be entered as an
   #   URL parameter 'uid=$uid' of that article.
   #   Return value: user ID of that guardian user
   #                 or 0 (missing URL parameter or wrong user ID)
   #   used functions:
   #      self::get_locale()
   #      self::rex_user_login($uid)
   #      self::session_set($login)
   #      self::session_end($login)
   #
   # --- get URL parameter
   $uid='';
   if(!empty($_GET['uid'])) $uid=$_GET['uid'];
   if(empty($uid)) return 0;
   #
   # --- get the user's login and (encrypted) password
   $guser=self::rex_user_login($uid);
   if(count($guser)<=0) return 0;
   $lognam  =$guser['login'];
   $password=$guser['password'];
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
     self::session_set($lognam);
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
# ----- print_file ----------------------------------------------------
public static function top_parent_media_category($file) {
   #   Returns the top parent_media_category of a media file.
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
public static function media_guardian_users($file,$tpmcatid) {
   #   Determines whether access to a media file is controlled by guardian users.
   #   The IDs of the guardian users are returned in a numbered array (numbering from 1).
   #   $file              given media file (relative file name)
   #   $tpmcatid          (>0) top parent_media_category of the given media file
   #   used functions:
   #      self::rex_guardian_users($cond)
   #      self::protected_categories_user($roles,$permtype)
   #
   $uid=array();
   if($tpmcatid<=0) return $uid;
   #
   $gusers=self::rex_guardian_users(0);
   $m=0;
   for($i=0;$i<count($gusers);$i=$i+1):
      $roles=$gusers[$i]['role'];
      $medcatid=self::protected_categories_user($roles,PERM_MEDIA);
      if(empty($medcatid)) continue;
      $mid=explode(',',$medcatid);
      for($k=0;$k<count($mid);$k=$k+1):
         if($mid[$k]==$tpmcatid):
           $m=$m+1;
           $uid[$m]=$gusers[$i]['id'];
           endif;
         endfor;
      endfor;
   return $uid;
   }
public static function print_file($file,$media_type='') {
   #   Displaying a media file (or an error file, if no access is allowed).
   #   $file              given media file (relative file name)
   #   $media_type        given media type
   #   used functions:
   #      self::top_parent_media_category($file)
   #      self::media_guardian_user($file,$tpmcatid)
   #      self::get_rex_editor()
   #      self::session_get()
   #      self::rex_user_login($uid)
   #      rex_media_manager::sendMedia()
   #      rex_response::setHeader('Last-Modified','...')
   #      rex_response::sendFile($file,$contentType,$contentDisposition)
   #
   # --- rex_media_manager object and absolute path to the media file
   if(empty($media_type)):
     $medfile=rex_path::media($file);
     $managed_media=new rex_managed_media($medfile);
     $manager=new rex_media_manager($managed_media);
     else:
     $counter=rex_media_manager::deleteCache($file,$media_type);
     $manager=rex_media_manager::create($media_type,$file);
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
   $mtype='';
   if(file_exists($medfile)) $mtype=mime_content_type($medfile);
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
   $tpmcatid=self::top_parent_media_category($file);
   if($tpmcatid>0):
     $protid=self::media_guardian_users($file,$tpmcatid);
     for($i=1;$i<=count($protid);$i=$i+1)
        if($protid[$i]>0):
          $protected=TRUE;
          break;
          endif;
     endif;
   #
   # --- access allowed?
   $allowed=TRUE;
   if($protected):
     #     site administrator or Redaxo editor has signed on?
     $son=FALSE;
     $auth=self::get_rex_editor();
     if($auth['id']==1) $son=TRUE;   // site administrator
     if($auth['id']>=2):
       $arr=explode(',',$auth['medcatid']);
       for($k=0;$k<count($arr);$k=$k+1)
          if($arr[$k]==$tpmcatid) $son=TRUE;   // Redaxo editor
       endif;
     for($i=1;$i<=count($protid);$i=$i+1):
        $session=self::session_get();
        $guser=self::rex_user_login($protid[$i]);
        if($session==$guser['login']) $son=TRUE;   // guardian user
        endfor;
     #     not signed in
     if(!$son) $allowed=FALSE;
     endif;
   #
   # --- output file
   if($allowed):
     rex_response::setHeader('Last-Modified','Wed, 20 Oct 2100 07:28:00 GMT1');
     if(substr($mtype,0,5)=='image' or $mtype=='application/pdf'):
       $manager->sendMedia();  // images and pdf documents
       else:
       rex_response::sendFile($medfile,$mtype,'attachment');  // accounting large files
       endif;
     else:
   #
   # --- output error file (file protected)
     $errfile=rex_path::addonAssets(CONTROL,'protected.gif');
     $managed_media=new rex_managed_media($errfile);
     $manager=new rex_media_manager($managed_media);
     rex_response::setHeader('Last-Modified','Wed, 20 Oct 2100 07:28:00 GMT1');
     $manager->sendMedia();
     endif;
   }
}
?>