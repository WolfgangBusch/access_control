<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2021
 */
define('CONTROL',     'access_control');  // Package-ID
define('USER_PARAMS', 'login,id,description,role');   // rex_user parameters
define('CACHE_FILE',  'guardian_users.json');
define('PROTECTOR',   'Protector');      // description of guardian user
define('GUARDIAN',    'Guardian');       // description of guardian user for the prohibited area
define('EDITOR',      'Editor');         // description of authenticated Redaxo editor
define('VISITOR',     'Visitor');        // description of authenticated visitor
define('PERM_STRUC',  'structure');      // rex_user_role complex_perm 'structure'
define('PERM_MEDIA',  'media');          // rex_user_role complex_perm 'media'
#
class access_control {
#
# ----- functions -----------------------------------------------------
#   installation functions:
#      cache_guardian_users()
#         get_guardian_users()
#            guardian_permissions($uid,$permkey)
#   session functions:
#      session_set($type,$user)
#      session_get($type)
#      session_end($user)
#   protecting categories functions:
#      protected_or_prohibited()     alias: protected_or_forbidden()
#         guardian_users()
#            cache_guardian_users()
#         rex_editor()
#            guardian_permissions($uid,$permkey)
#            session_get($type)
#            session_set($type,$user)
#         protected_or_prohibited_intern($art_id,$gusers,$rexeditor)
#            article_guardian_users($art_id,$gusers)
#            session_get($type)
#   protecting media categories functions:
#      print_file($gusers,$rexeditor,$file,$media_type='')
#         media_guardian_users($file,$gusers)
#            top_parent_media_category($file)
#         top_parent_media_category($file)
#         get_rex_user($uid)
#         session_get($type)
#   login_page()
#      get_rex_user($uid)
#      session_get($type)
#      session_end($user)
#      locale()
#      guardian_users()
#         cache_guardian_users()
#      session_set($type,$user)
#
# ----- installation functions ----------------------------------------
public static function cache_guardian_users() {
   #   Reads the guardian users from table rex_user and stores them as
   #   fixed values in a cache file.
   #   used functions:
   #      self::get_guardian_users()
   #
   # --- read the guardian users from table rex_user
   $gusers=self::get_guardian_users();
   #
   # --- store the array in a cache file
   $cachefile=rex_path::addonCache('access_control',CACHE_FILE);
   return rex_file::put($cachefile,json_encode($gusers));
   }
public static function get_guardian_users() {
   #   Returns all guardian users in a numbered array (numbering from 1)
   #   whose elements are associative arrays containing
   #      user parameters:
   #      ['login']       rex_user column 'login'
   #      ['id']          rex_user column 'id'
   #      ['description'] rex_user column 'description'
   #      other parameters:
   #      [PERM_STRUC]    numbered array of IDs of categories
   #                      that the user has access to (numbering from 0)
   #      [PERM_MEDIA]    numbered array of IDs of media categories
   #                      that the user has access to (numbering from 0)
   #   used functions:
   #      self::guardian_permissions($uid,$permkey)
   #
   $where='description=\''.PROTECTOR.'\' OR description=\''.GUARDIAN.'\'';
   #
   # --- read all users who meet the given condition
   $sql=rex_sql::factory();
   $users=$sql->getArray('SELECT '.USER_PARAMS.' FROM rex_user WHERE '.$where);
   $us=array();
   $m=0;
   for($i=0;$i<count($users);$i=$i+1):
      #     a single user
      $user=$users[$i];
      $keys=array_keys($user);
      $gus=array();
      for($k=0;$k<count($keys);$k=$k+1) if($keys[$k]!='role') $gus[$keys[$k]]=$user[$keys[$k]];
      #     and his permissions
      $uid=$user['id'];
      $gus[PERM_STRUC]=self::guardian_permissions($uid,PERM_STRUC);
      $gus[PERM_MEDIA]=self::guardian_permissions($uid,PERM_MEDIA);
      $m=$m+1;
      $us[$m]=$gus;
      endfor;
   return $us;
   }
public static function guardian_permissions($uid,$permkey) {
   #   Determines either the category Ids or the media category Ids to which
   #   a given guardian user has access. The Ids are returned in a numbered
   #   array (numbering from 0).
   #   $uid               guardian user's Id [rex_user column 'id']
   #   $permkey           =PERM_STRUC (access to categories) or
   #                      =PERM_MEDIA (access to media categories)
   #
   $sql=rex_sql::factory();
   $query='SELECT role FROM rex_user WHERE id='.$uid;
   $row=$sql->getArray($query);
   $rolestr=$row[0]['role'];
   $role=explode(',',$rolestr);
   #
   # --- permitted ids collected from all roles
   $permid=array();
   $m=0;
   $pmcol='';
   for($i=0;$i<count($role);$i=$i+1):
      if(empty($role[$i])) continue;
      $query='SELECT perms FROM rex_user_role WHERE id='.$role[$i];
      $row=$sql->getArray($query);
      $perms=$row[0]['perms'];
      $arr=json_decode($perms,TRUE);
      $str=$arr[$permkey];
      $brr=explode('|',$str);
      #     collected string of permitted ids like $pmcol='|12|4|...|35'
      for($j=0;$j<count($brr);$j=$j+1) if(!empty($brr[$j])) $pmcol=$pmcol.'|'.$brr[$j];
      #     remove double occurring permitted ids
      $arr=explode('|',$pmcol);
      for($k=1;$k<count($arr);$k=$k+1):
         for($j=0;$j<$m;$j=$j+1)
            if($arr[$k]==$permid[$j]):
              $arr[$k]='';
              endif;
         if(!empty($arr[$k])):
           $permid[$m]=$arr[$k];
           $m=$m+1;
           endif;
         endfor;
      endfor;
   return $permid;
   }
#
# ----- session functions ---------------------------------------------
public static function session_set($type,$user) {
   #   Store an authenticated user as an associative array in session variables:
   #   $type              =VISITOR: a guardian user has authenticated via sign-in page
   #                      =EDITOR:  a Redaxo editor has authenticated via backend sign-in
   #   $user              Array of the authenticated user: $_SESSION[CONTROL][$type][]
   #     ['login']        rex_user column 'login'
   #     ['id']           rex_user column 'id'
   #     [PERM_STRUC]     numbered array of IDs of categories
   #                      that the user has access to (numbering from 0)
   #     [PERM_MERDIA]    numbered array of IDs of media categories
   #                      that the user has access to (numbering from 0)
   #
   if(session_status()!=PHP_SESSION_ACTIVE) session_start();
   $keys=array_keys($user);
   $sesuser=array();
   for($i=0;$i<count($keys);$i=$i+1)
      if($keys[$i]!='description') $sesuser[$keys[$i]]=$user[$keys[$i]];
   if(count($sesuser)>0) $_SESSION[CONTROL][$type]=$sesuser;
   }
public static function session_get($type) {
   #   Returns an authenticated user as array of session variables, see above.
   #   $type              =VISITOR: returns an authenticated guardian user
   #                      =EDITOR:  returns an authenticated Redaxo editor
   #
   if(session_status()!=PHP_SESSION_ACTIVE) session_start();
   $user=array();
   if(isset($_SESSION[CONTROL][$type])) $user=$_SESSION[CONTROL][$type];
   return $user;
   }
public static function session_end($user) {
   #   Ends session.
   #   $user              array of the guardian user having authenticated
   #                      via sign-in page
   #
   if(isset($user)):
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     $sesuser=array();
     if(isset($_SESSION[CONTROL][VISITOR])) $sesuser=$_SESSION[CONTROL][VISITOR];
     if($sesuser==$user) unset($_SESSION[CONTROL][VISITOR]);
     endif;
   }
#
# ----- protecting categories functions -------------------------------
public static function protected_or_forbidden() {
   #   Alias for the following function
   #
   self::protected_or_prohibited();
   }
public static function protected_or_prohibited() {
   #   Determines whether the access to the current article is allowed or not.
   #   Returns the following value:
   #      =0      access is allowed because
   #              - the article is public or
   #              - the visitor has authenticated via sign-in page
   #                (giving login and password of one of the guardian users
   #                protecting a category containing the article) or
   #              - the visitor is authorized having signed in as Redaxo editor
   #                in the backend
   #      =1      access denied because
   #              - the article is located in the prohibited category and
   #              - the visitor has not authenticated as site adminstrator
   #                in the backend
   #      >1      access denied because
   #              - the article is located in one of the protected categories and
   #              - the visitor has not authenticated via sign-in page
   #                (giving login and password of one of the guardian users
   #                protecting a category containing the article) or
   #              = Id of the guarding user who controls access to the category
   #                that contains the current article
   #   used functions:
   #      self::guardian_users()
   #      self::rex_editor()
   #      self::protected_or_prohibited_intern($art_id,$gusers,$rexeditor)
   #   Exemplary section on top of the page template, shows an error message or
   #   presents a sign-in form instead of the article content
   #      ...
   #      $uid=access_control::protected_or_prohibited();
   #      if($uid>0):
   #         if($uid==1):
   #          echo '<p>prohibited area, reserved for the site administrator</p>';
   #          else:
   #          $path='/login.html';   // path to the sign-in page,  t o   b e   r e p l a c e d   individually
   #          echo '<p>article in protected area, access requires authentication</p>';
   #          echo '<p><a href="'.$path.'?uid='.$uid.'">Sign in</a></p>';
   #          endif;
   #        else:
   #        echo $this->getArticle(); // article content
   #        endif;
   #      ...
   #
   # --- collect all guardian users from cache
   $gusers=self::guardian_users();
   #
   # --- Redaxo editor authenticated in backend
   $rexeditor=self::rex_editor();
   #
   # --- access to the article allowed?
   $art_id=rex_article::getCurrentId();
   $uid=self::protected_or_prohibited_intern($art_id,$gusers,$rexeditor);
   return $uid;
   }
public static function guardian_users() {
   #   Returns all guardian users in a numbered array (numbering from 1).
   #   whose elements are associative arrays containing
   #      user parameters:
   #      ['login']       rex_user column 'login'
   #      ['id']          rex_user column 'id'
   #      ['description'] rex_user column 'description'
   #      other parameters:
   #      [PERM_STRUC]    numbered array of IDs of categories
   #                      that the user has access to (numbering from 0)
   #      [PERM_MEDIA]    numbered array of IDs of media categories
   #                      that the user has access to (numbering from 0)
   #   The data are read from a cache file.
   #   used functions:
   #      self::cache_guardian_users()
   #
   $cachefile=rex_path::addonCache(CONTROL,CACHE_FILE);
   if(!file_exists($cachefile)) self::cache_guardian_users();
   $gusers=rex_file::getCache($cachefile);
   return $gusers;
   }
public static function rex_editor() {
   #   Returns the Redaxo editor in an assocative array if he has authenticated:
   #      ['login']       rex_user column 'login'
   #      ['id']          rex_user 'id'
   #      [PERM_STRUC]    numbered array of IDs of categories
   #                      that the user has access to (numbering from 0)
   #      [PERM_MEDIA]    numbered array of IDs of media categories
   #                      that the user has access to (numbering from 0)
   #   The editor's user parameter array is taken from this session array:
   #                      $_SESSION[CONTROL][EDITOR]
   #   If there is no such session array it is set here.
   #   used functions:
   #      self::guardian_permissions($uid,$permkey)
   #      self::session_get($type)
   #      self::session_set($type,$user)
   #
   # --- get Redaxo editor from session array
   $editor=self::session_get(EDITOR);
   #
   # --- get current Redaxo editor from database
   $beuser=rex_backend_login::createUser();
   #
   # --- if new Redaxo editor update the session array
   if($beuser==null):
     $editor=array();
     else:
     if(count($editor)<=0):
       $edlogin='';
       else:
       $edlogin=$editor['login'];
       endif;
     $belogin=$beuser->getLogin();
     if($belogin!=$edlogin):
       $editor=array();
       $uid=$beuser->getId();
       $editor['login']   =$belogin;
       $editor['id']      =$uid;
       $editor[PERM_STRUC]=self::guardian_permissions($uid,PERM_STRUC);
       $editor[PERM_MEDIA]=self::guardian_permissions($uid,PERM_MEDIA);
       endif;
     endif;
   self::session_set(EDITOR,$editor);
   #
   return $editor;
   }
public static function protected_or_prohibited_intern($art_id,$gusers,$rexeditor) {
   #   Determines whether the access to the current article is allowed or not.
   #   Return value (see above)
   #   $art_id            Id of the current article
   #   $gusers            array of guarding users
   #   $rexeditor         array of data of the Redaxo editor having signed in in the backend
   #   used functions:
   #      self::article_guardian_users($art_id,$gusers)
   #      self::session_get($type)
   #
   # --- guardian users who control access to a category that contains the article
   $art_gusers=self::article_guardian_users($art_id,$gusers);
   #
   # --- proof whether the access to the current article is allowed
   $prot=array();
   #
   # --- article not located in a protected or prohibited category
   if(count($art_gusers)<=0) $prot=array('id'=>0,'protect'=>0);
   #
   # --- site administrator, signed in
   if(count($prot)<=0):
     if(count($rexeditor)>0):
       $redid=$rexeditor['id'];
       if($redid==1) $prot=array('id'=>$redid,'protect'=>0);
       endif;
     endif;
   #
   # --- prohibited area, access denied
   if(count($prot)<=0):
     for($i=1;$i<=count($art_gusers);$i=$i+1)
        if($art_gusers[$i]['description']==GUARDIAN)
          $prot=array('id'=>$art_gusers[$i]['id'],'protect'=>1);
     endif;
   #
   # --- protected area, Redaxo editor signed in
   if(count($prot)<=0):
     if(count($rexeditor)>0):
       $redid=$rexeditor['id'];
       if($redid>=2):
         $catid=$rexeditor[PERM_STRUC];
         for($i=1;$i<=count($art_gusers);$i=$i+1):
            $cid=$art_gusers[$i][PERM_STRUC];
            for($k=0;$k<count($catid);$k=$k+1)
               if($catid[$k]==$cid) $prot=array('id'=>$redid,'protect'=>0);
            endfor;
         endif;
       endif;
     endif;
   #
   # --- protected category, visitor authenticated
   if(count($prot)<=0):
     $visitor=self::session_get(VISITOR);
     if(count($visitor)>0):
       $uid=0;
       for($i=1;$i<=count($art_gusers);$i=$i+1)
          if($art_gusers[$i]['id']==$visitor['id']):
            $uid=$art_gusers[$i]['id'];
            break;
            endif;
       if($uid>0) $prot=array('id'=>$uid,'protect'=>0);
       endif;
     endif;
   #
   # --- protected category, not authenticated
   if(count($prot)<=0):
     $uidstr='';
     for($i=1;$i<=count($art_gusers);$i=$i+1):
        if($art_gusers[$i]['description']==GUARDIAN) continue;
        $uidstr=$uidstr.','.$art_gusers[$i]['id'];
        endfor;
     if(!empty($uidstr)) $uidstr=substr($uidstr,1);
     $prot=array('id'=>$uidstr,'protect'=>2);
     endif;
   #
   $uid=$prot['id'];
   if($prot['protect']==1) $uid=1;
   if($prot['protect']==0) $uid=0;
   return $uid;
   }
public static function article_guardian_users($art_id,$gusers) {
   #   Returns the users controlling the access to a category of a given
   #   article (guardian users) in a numbered array (numbering from 1).
   #   Each user is represented as an associative array containing
   #      ['login']       his login (column 'login')
   #      ['id']          his Id (column 'id')
   #      ['description'] his description (column 'description')
   #      [PERM_STRUC]    the Id of the protected category containing the article
   #   $art_id            Id of the given article
   #   $gusers            array of guarding users
   #
   # --- article located in any protected or prohibited category?
   $cat_id='';
   $art=rex_article::get($art_id);
   for($i=1;$i<=count($gusers);$i=$i+1):
      $catid=$gusers[$i][PERM_STRUC];
      for($k=0;$k<count($catid);$k=$k+1):
         $cid=$catid[$k];
         if($art_id==$cid):
           $cat_id=$cid;
           break;
           endif;
         $brr=explode('|',$art->getValue('path'));
         for($j=0;$j<count($brr);$j=$j+1):
            if($brr[$j]==$cid):
              $cat_id=$cid;
              break;
              endif;
            endfor;
         endfor;
         if($cat_id>0) break;
      endfor;
   if(empty($cat_id)) return array();
   #
   # --- article located in one of the protected or prohibited categories
   $m=0;
   $prot=array();
   for($i=1;$i<=count($gusers);$i=$i+1):
      $catid=$gusers[$i][PERM_STRUC];
      for($k=0;$k<count($catid);$k=$k+1)
         if($catid[$k]==$cat_id):
           $m=$m+1;
           $prot[$m]=array('id'=>$gusers[$i]['id'],'login'=>$gusers[$i]['login'],
                          'description'=>$gusers[$i]['description'],PERM_STRUC=>$cat_id);
           endif;
      endfor;
   return $prot;
   }
#
# ----- protecting media categories functions -------------------------
public static function print_file($file,$media_type='') {
   #   Displaying a media file (or an error file, if no access is allowed).
   #   $file              given media file (relative file name)
   #   $media_type        given media type
   #   used functions:
   #      self::guardian_users()
   #      self::rex_editor()
   #      self::media_guardian_users($file,$gusers)
   #      self::top_parent_media_category($file)
   #      self::session_get($type)
   #      self::get_rex_user($uid)
   #      rex_media_manager::sendMedia()
   #      rex_response::setHeader('Last-Modified','...')
   #      rex_response::sendFile($file,$contentType,$contentDisposition)
   #
   $gusers=self::guardian_users();
   $rexeditor=self::rex_editor();
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
     $errfile=rex_path::addon('media_manager','media/warning.jpg');
     $managed_media=new rex_managed_media($errfile);
     $manager=new rex_media_manager($managed_media);
     $manager->sendMedia();
     endif;
   #
   # --- media file protected?
   $protected=FALSE;
   $protid=self::media_guardian_users($file,$gusers);
   if(count($protid)>0) $protected=TRUE;
   #
   # --- access allowed?
   $allowed=TRUE;
   if($protected):
     #     site administrator or Redaxo editor has signed in?
     $son=FALSE;
     if($rexeditor['id']==1) $son=TRUE;   // site administrator
     if($rexeditor['id']>=2):
       $tpmcatid=self::top_parent_media_category($file);
       $catid=$rexeditor[PERM_MEDIA];
       for($k=0;$k<count($catid);$k=$k+1)
          if($catid[$k]==$tpmcatid) $son=TRUE;   // Redaxo editor
       endif;
     if(!$son):
       #     visitor has signed in?
       $sesuser=self::session_get(VISITOR);
       for($i=1;$i<=count($protid);$i=$i+1):
          $visitor=self::get_rex_user($protid[$i]);
          if($sesuser['login']==$visitor['login']):
            $son=TRUE;   // guardian user
            break;
            endif;
          endfor;
       endif;
     #     not signed in
     if(!$son) $allowed=FALSE;
     endif;
   #
   # --- output file
   if($allowed):
     rex_response::setHeader('Last-Modified','Wed, 20 Oct 2100 07:28:00 GMT1');
     if(substr($mtype,0,5)=='image' or $mtype=='application/pdf' or $mtype='plain/text'):
       $manager->sendMedia();  // images, pdf documents, and text documents
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
public static function media_guardian_users($file,$gusers) {
   #   Determines whether access to a media file is controlled by guardian users.
   #   The Ids of the guardian users are returned in a numbered array (numbering from 1).
   #   $file              given media file (relative file name: /media/$file)
   #   $gusers            array of guarding users
   #   used functions:
   #      self::top_parent_media_category($file)
   #
   $uid=array();
   $tpmcatid=self::top_parent_media_category($file);
   if($tpmcatid<=0) return $uid;
   #
   $m=0;
   for($i=1;$i<=count($gusers);$i=$i+1):
      $user=$gusers[$i];
      $userid=$user['id'];
      $mid=$user[PERM_MEDIA];
      for($k=0;$k<count($mid);$k=$k+1)
         if($mid[$k]==$tpmcatid):
           $m=$m+1;
           $uid[$m]=$userid;
           endif;
      endfor;
   return $uid;
   }
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
#
# ----- login_page ----------------------------------------------------
public static function login_page() {
   #   Displays an article for authentication of a guardian user (sign-in page).
   #      Input values:
   #   $_GET['uid']       comma-separated list of Ids of guardian users that are
   #                      allowed to be authenticated on the sign-in page.
   #                      If empty the list is augmented to the Ids of all
   #                      guardian users defined.
   #   $_POST['action']   If empty the form parameters will be checked.
   #                      Otherwise the authenticated user will be signed off.
   #   $_POST['login']    Guardian user's login, to proof.
   #   $_POST['passwd']   Guardian user's password (not encrypted), to proof.
   #      Return value:
   #                      Guardian user's Id if authenticated or
   #                      comma-seperated list read in via $_GET['uid'] otherwise.
   #   used functions:
   #      self::guardian_users()
   #      self::get_rex_user($uid)
   #      self::session_get($type)
   #      self::session_end($user)
   #      self::locale()
   #      self::session_set($type,$user)
   #
   # --- get guardian user's Id, if empty switch to ALL guardian users Ids
   $uidstr='';
   if(!empty($_GET['uid'])) $uidstr=$_GET['uid'];
   if(empty($uidstr)):
     $gusers=self::guardian_users();
     for($i=1;$i<=count($gusers);$i=$i+1)
        if($gusers[$i]['description']!=GUARDIAN) $uidstr=$uidstr.','.$gusers[$i]['id'];
     if(strlen($uidstr)>=1) $uidstr=substr($uidstr,1);
     endif;
   #
   $logoff='';
   $action='';
   if(!empty($_POST['action'])) $action=$_POST['action'];
   #
   # --- anyone already logged in?
   $visitor=self::session_get(VISITOR);
   if(count($visitor)>0):
     #     in this case display a logoff button, only
     if(empty($action)):
       $uidstr=$visitor['id'];
       $login=$visitor['login'];
       $_POST['login']=$login;
       $logoff='logoff';
       endif;
     endif;
   #
   # --- get the user logins and (encrypted) passwords
   $uid=explode(',',$uidstr);
   for($i=0;$i<count($uid);$i=$i+1):
      $guser=self::get_rex_user($uid[$i]);
      if(count($guser)<=0) return 0;
      $lognam[$i]  =$guser['login'];
      $password[$i]=$guser['password'];
      endfor;
   #
   # --- get input login name and password ...
   $login ='';
   $passwd='';
   if(!empty($_POST['login']))  $login =$_POST['login'];
   if(!empty($_POST['passwd'])) $passwd=$_POST['passwd'];
   #
   # --- ... or sign off (delete session variables)
   if(!empty($action)):
     $sesuser=self::session_get(VISITOR);
     self::session_end($sesuser);
     endif;
   #
   # --- setLocale
   rex_i18n::setLocale(self::locale());
   #
   # --- analysing the input values
   $error='';
   #     empty login?
   if(empty($login)) $error=rex_i18n::rawMsg('access_control_signin_in_username_pwd');
   #     wrong login?
   if(empty($error)):
     $num=-1;
     for($i=0;$i<count($uid);$i=$i+1)
        if($login==$lognam[$i]):
          $num=$i;
          break;
          endif;
     if($num<0) $error=rex_i18n::rawMsg('access_control_signin_wrong_username');
     endif;
   #     empty password?
   if(empty($error) and empty($passwd)
      and empty($logoff))   // generating a logoff page
     $error=rex_i18n::rawMsg('access_control_signin_in_pwd');
   #     wrong password?
   if(empty($error)
      and empty($logoff)):   // generating a logoff page
     $ok=rex_login::passwordVerify($passwd,$password[$num]);
     if(!$ok) $error=rex_i18n::rawMsg('access_control_signin_wrong_pwd');
     endif;
   #
   # --- display the form
   echo '
<div class="access_control_frame">
<form method="post">
<table>';
   if(!empty($error)):
     #     sign-in form
     echo '
    <tr><td>'.rex_i18n::rawMsg('access_control_signin_username').': &nbsp;</td>
        <td><input type="text" name="login" value="'.$login.'" class="access_control_input" /></td></tr>
    <tr><td>'.rex_i18n::rawMsg('access_control_signin_pwd').':</td>
        <td><input type="password" name="passwd" value="'.$passwd.'" class="access_control_input" /></td></tr>
    <tr><td colspan=2">
            <p class="access_control_error">'.$error.'</p></td>
    <tr><td></td>
        <td><button type="submit" name="action" value="">
            '.rex_i18n::rawMsg('access_control_signin_button_in').'</button></td></tr>';
     #     return value
     $uidret=$uidstr;
     else:
     #     set session user
     $gusers=self::guardian_users();
     $sesuser=array();
     for($i=1;$i<=count($gusers);$i=$i+1)
        if($gusers[$i]['id']==$uid[$num]) $sesuser=$gusers[$i];
     self::session_set(VISITOR,$sesuser);
     #     sign-off form
     $success=rex_i18n::rawMsg('access_control_signin_user').' \''.$lognam[$num].'\' '.
              rex_i18n::rawMsg('access_control_signin_authenticated');
     echo '
    <tr><td><input type="hidden" name="login" value="" />
            <input type="hidden" name="passwd" value="" /></td>
        <td><p class="access_control_success">'.$success.'</p></td></tr>
    <tr><td></td>
        <td><button type="submit" name="action" value="'.rex_i18n::rawMsg('access_control_signin_val_off').'">
            '.rex_i18n::rawMsg('access_control_signin_button_off').'</button></td></tr>';
     #     return value
     $uidret=$uid[$num];
     endif; 
   echo '
</table>
</form>
</div>
';
   return $uidret;
   }
public static function locale() {
   #   Returns the locale of the current article ('de_de' or 'en_gb').
   #
   $loc_all=rex_i18n::getLocales();
   $code   =rex_clang::get(rex_clang::getCurrentId())->getCode();
   $locale ='';
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
public static function get_rex_user($uid) {
   #   Returns data of an user from table rex_user
   #   (rex_user column 'login', 'password').
   #   $uid               Id of the given user
   #
   if($uid<=0) return array();
   #
   $sql=rex_sql::factory();
   $users=$sql->getArray('SELECT login,password FROM rex_user WHERE id='.$uid);
   if(count($users)>0) return $users[0];
   return array();
   }
}
?>