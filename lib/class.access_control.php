<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Oktober 2023
 */
#
class access_control {
#   basic functions:
#      guardian_users()
#         cache_guardian_users()
#            get_guardian_users()
#               guardian_permissions($uid,$permkey)
#      session_set($type,$user)
#      session_get($type)
#      session_end($user)
#      access_allowed($uid)
#         get_locale()
#      get_rex_user($uid)
#   protecting categories functions:
#      protected_or_prohibited()
#         rex_editor()
#         protected_or_prohibited_intern($art_id,$gusers,$rexeditor)
#            article_guardian_users($art_id,$gusers)
#   protecting media categories functions:
#      control_file($mediatype,$file)
#         media_guardian_users($mediatype,$file,$gusers)
#            top_parent_media_category($mediatype,$file)
#   sign-in page:
#      write_signin_page()
#      signin_page()
#
# ----- constants -----------------------------------------------------
const this_addon =__CLASS__;          // Package-ID
const signin_page='/signin_page.php'; // filename of the sign-in page
const user_params='login,id,description,role';  // rex_user parameters
const cache_file ='guardian_users.json';        // cache file
const protector  ='Protector';        // description of guardian user
const guardian   ='Guardian';         // description of guardian user for the prohibited area
const perm_struc ='structure';        // rex_user_role complex_perm 'structure'
const perm_media ='media';            // rex_user_role complex_perm 'media'
const editor     ='Editor';           // session parameter of a signed-in Redaxo editor
const visitor    ='Visitor';          // session parameter of an authenticated visitor
const cat_marker ='ZZmedia:';         // first part of top media categories names
#
# ----- basic functions -----------------------------------------------
public static function guardian_users() {
   #   Returns all guardian users in a numbered array (numbering from 1).
   #   whose elements are associative arrays containing
   #      user parameters:
   #      ['login']           rex_user column 'login'
   #      ['id']              rex_user column 'id'
   #      ['description']     rex_user column 'description'
   #      other parameters:
   #      [self::perm_struc]  numbered array of IDs of categories
   #                          that the user has access to (numbering from 0)
   #      [self::perm_media]  numbered array of IDs of media categories
   #                          that the user has access to (numbering from 0)
   #   The data are read from a cache file.
   #   used functions:
   #      self::cache_guardian_users()
   #   used constants:
   #      self::this_addon
   #      self::cache_file
   #
   $cachefile=rex_path::addonCache(self::this_addon,self::cache_file);
   if(!file_exists($cachefile)) self::cache_guardian_users();
   $gusers=rex_file::getCache($cachefile);
   return $gusers;
   }
public static function cache_guardian_users() {
   #   Reads the guardian users from the tables rex_user and rex_user_role
   #   and stores them as fixed values in a cache file. Returns TRUE/FALSE
   #   depending on whether the cache file has been successfully saved or not.
   #   used functions:
   #      self::get_guardian_users()
   #   used constants:
   #      self::this_addon
   #      self::cache_file
   #
   # --- read the guardian users from table rex_user
   $gusers=self::get_guardian_users();
   #
   # --- store the array in a cache file
   $cachefile=rex_path::addonCache(self::this_addon,self::cache_file);
   return rex_file::put($cachefile,json_encode($gusers));
   }
public static function get_guardian_users() {
   #   Returns all guardian users in a numbered array (numbering from 1)
   #   whose elements are associative arrays. The guardian user's array
   #   contains the user parameters:
   #      ['login']           rex_user column 'login'
   #      ['id']              rex_user column 'id'
   #      ['description']     rex_user column 'description'
   #      other parameters:
   #      [self::perm_struc]  numbered array of IDs of categories
   #                          that the user has access to (numbering from 0)
   #      [self::perm_media]  numbered array of IDs of media categories
   #                          that the user has access to (numbering from 0)   
   #   The data are retrieved from the tables rex_user and rex_user_role.
   #   used functions:
   #      self::guardian_permissions($uid,$permkey)
   #   used constants:
   #      self::user_params
   #      self::protector
   #      self::guardian
   #      self::perm_struc
   #      self::perm_media
   #
   $where='description=\''.self::protector.'\' OR description=\''.self::guardian.'\'';
   #
   # --- read all users who meet the given condition
   $sql=rex_sql::factory();
   $users=$sql->getArray('SELECT '.self::user_params.' FROM rex_user WHERE '.$where);
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
      $gus[self::perm_struc]=self::guardian_permissions($uid,self::perm_struc);
      $gus[self::perm_media]=self::guardian_permissions($uid,self::perm_media);
      $m=$m+1;
      $us[$m]=$gus;
      endfor;
   return $us;
   }
public static function guardian_permissions($uid,$permkey) {
   #   Determines either the category Ids or the media category Ids that a guardian
   #   user protects. The Ids are returned in a numbered array (numbering from 0).
   #   The data are retrieved from the tables rex_user and rex_user_role.
   #   $uid               guardian user's Id [rex_user column 'id']
   #   $permkey           =self::perm_struc (access to categories) or
   #                      =self::perm_media (access to media categories)
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
      $str='';
      if(isset($arr[$permkey])) $str=$arr[$permkey];
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
public static function session_set($type,$user) {
   #   Stores an authenticated user as an associative session array:
   #   $type                 =self::visitor: a visitor user has authenticated via sign-in page
   #                         =self::editor:  a Redaxo editor has authenticated via backend sign-in
   #   $user                 Array of the authenticated user, containing these keys:
   #     ['login']           rex_user column 'login'
   #     ['id']              rex_user column 'id'
   #     [self::perm_struc]  numbered array of IDs of categories that either a Redaxo
   #                         editor has access to or that the guardian user protects
   #                         (numbering from 0)
   #     [self::perm_media]  numbered array of IDs of media categories that either a Redaxo
   #                         editor has access to or that the guardian user protects
   #                         (numbering from 0)
   #   So the session aray takes this form:
   #      $_SESSION[self::this_addon][$type]=$user
   #      ($type=self::editor or $type=self::visitor)
   #   example:
   #      $_SESSION[self::this_addon][self::visitor]['login']         =$user['login']
   #      $_SESSION[self::this_addon][self::visitor]['id']            =$user['id']
   #      $_SESSION[self::this_addon][self::visitor][self::perm_struc]=$user[self::perm_struc]                  [self::perm_struc]
   #      $_SESSION[self::this_addon][self::visitor][self::perm_media]=$user[self::perm_media]                  [self::perm_struc]
   #   used constants:
   #      self::this_addon
   #
   if(session_status()!=PHP_SESSION_ACTIVE) session_start();
   $keys=array_keys($user);
   $sesuser=array();
   for($i=0;$i<count($keys);$i=$i+1)
      if($keys[$i]!='description') $sesuser[$keys[$i]]=$user[$keys[$i]];
   if(count($sesuser)>0) $_SESSION[self::this_addon][$type]=$sesuser;
   }
public static function session_get($type) {
   #   Returns an authenticated visitor or an Redaxo editor signed in in the backend
   #   as a session array, see above.
   #   $type              =self::visitor:  authenticated visitor
   #                      =self::editor:   signed in Redaxo editor
   #   used constants:
   #      self::this_addon
   #
   if(session_status()!=PHP_SESSION_ACTIVE) session_start();
   $user=array();
   if(isset($_SESSION[self::this_addon][$type])) $user=$_SESSION[self::this_addon][$type];
   return $user;
   }
public static function session_end($user) {
   #   Ends session. Signs off an authenticated visitor.
   #   $user              array of the visitor having authenticated via sign-in page
   #   used constants:
   #      self::this_addon
   #      self::visitor
   #
   if(isset($user)):
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     $sesuser=array();
     if(isset($_SESSION[self::this_addon][self::visitor])) $sesuser=$_SESSION[self::this_addon][self::visitor];
     if($sesuser==$user) unset($_SESSION[self::this_addon][self::visitor]);
     endif;
   }
public static function get_locale() {
   #   Returns the locale of the current article.
   #
   $locale='';
   $art=rex_article::getCurrent();
   $clang_id=$art->getClangId();
   $code=rex_clang::get($clang_id)->getCode();
   $locales=rex_i18n::getLocales();
   for($i=0;$i<count($locales);$i=$i+1):
      $arr=explode('_',$locales[$i]);
      if($arr[0]==$code):
        return $locale=$code.'_'.$arr[1];
        break;
        endif;
      endfor;
   if(empty($locale)) $locale=rex_i18n::getLocale();
   return $locale;
   }
public static function access_allowed($uid,$artid=0) {
   #   Makes the decision whether to allow read access to an article (normally
   #   the current article).
   #   Used in page template, only. ##########
   #   $uid              controls the contents of the return string:
   #                     <=0:  empty (means: access allowed)
   #                     =1:   message (access reserved for the site administrator)
   #                     >1:   message (access only after sign-in as a guardian user)
   #                           and link to an associated sign-in form
   #   $artid            Id of the article
   #                     if <=0: Id = rex_article::getCurrentId()
   #   used functions:
   #      self::get_locale()
   #   used constants:
   #      self::signin_page
   #
   # --- locale of the current article
   $locale=self::get_locale();
   rex_i18n::setLocale($locale);
   #
   # --- generate messages
   $art_id=$artid;
   if($art_id<=0) $art_id=rex_article::getCurrentId();
   $art=rex_article::get($art_id);
   $aus='';
   if(intval($uid)>0):
     #     message ($uid==1)
     if($uid==1):
       $parurl=rex_getUrl(rex_article::getSiteStartArticleId());
       $aus=$aus.'
<p>'.rex_i18n::rawMsg('ac_allowed_prohibited').'</p>';
       else:
       #     current article
       $art_name =$art->getName();
       $parent_id=$art->getParentId();
       $parart=rex_article::get($parent_id);
       if($parart->isStartArticle()) $parent_id=$parart->getParentId();
       $par_url=rex_getUrl($parent_id);
       $logurl=self::signin_page.'?uid='.$uid.'&locale='.$locale;
       $extras='width=400,height=250,top=20,left=20,titlebar=no,menubar=no,status=no,scollbars=no';
       $button='
<button onclick="window.open(\''.$logurl.'\',\'_blank\',\''.$extras.'\');"
        class="ac_button" title="'.rex_i18n::rawMsg('ac_allowed_button_title').'">
<a href=""><b>'.rex_i18n::rawMsg('ac_allowed_signin').'</b></a>
</button>.';
       #     link to sign-in page
       $aus=$aus.'
<p>'.rex_i18n::rawMsg('ac_allowed_article').' &nbsp; <i>"'.$art_name.'"</i> &nbsp; '.rex_i18n::rawMsg('ac_allowed_protected').'</span>,<br>
'.rex_i18n::rawMsg('ac_allowed_access').$button.'</p>';
       endif;
     $aus=$aus.'
<p><a href="'.$par_url.'">&larr; '.rex_i18n::rawMsg('ac_allowed_back').'</a></p>
';
     endif;
   return $aus;
   }
public static function get_rex_user($uid) {
   #   Returns some data of an user from table rex_user (columns 'login', 'password').
   #   $uid               Id of the given user
   #
   if($uid<=0) return array();
   #
   $sql=rex_sql::factory();
   $users=$sql->getArray('SELECT login,password FROM rex_user WHERE id='.$uid);
   if(count($users)>0) return $users[0];
   return array();
   }
#
# ----- protecting categories functions -------------------------------
public static function rex_editor() {
   #   Returns the Redaxo editor in an assocative array if he has authenticated:
   #      ['login']           rex_user column 'login'
   #      ['id']              rex_user 'id'
   #      [self::perm_struc]  numbered array of IDs of categories
   #                          that the user has access to (numbering from 0)
   #      [self::perm_media]  numbered array of IDs of media categories
   #                          that the user has access to (numbering from 0)
   #   The editor's user parameter array is taken from this session array:
   #                          $_SESSION[self::this_addon][self::editor]
   #   If there is no such session array it is set here.
   #   used functions:
   #      self::guardian_permissions($uid,$permkey)
   #      self::session_get($type)
   #      self::session_set($type,$user)
   #   used constants:
   #      self::editor
   #      self::perm_struc
   #      self::perm_media
   #
   # --- get Redaxo editor from session array
   $editor=self::session_get(self::editor);
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
       $editor[self::perm_struc]=self::guardian_permissions($uid,self::perm_struc);
       $editor[self::perm_media]=self::guardian_permissions($uid,self::perm_media);
       endif;
     endif;
   self::session_set(self::editor,$editor);
   #
   return $editor;
   }
public static function article_guardian_users($art_id,$gusers) {
   #   Returns the guardian users protecting the access to a category of a given
   #   article in a numbered array (numbering from 1).
   #   Each user is represented as an associative array containing
   #      ['login']           his login (column 'login')
   #      ['id']              his Id (column 'id')
   #      ['description']     his description (column 'description')
   #      [self::perm_struc]  the Id of the protected category containing the article
   #   $art_id                Id of the given article
   #   $gusers                array of guarding users
   #   used constants:
   #      self::perm_struc
   #
   $art=rex_article::get($art_id);
   if($art==null) return array();   
   #
   # --- article located in any protected or prohibited category?
   $cat_id='';
   for($i=1;$i<=count($gusers);$i=$i+1):
      $catid=$gusers[$i][self::perm_struc];
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
      $catid=$gusers[$i][self::perm_struc];
      for($k=0;$k<count($catid);$k=$k+1)
         if($catid[$k]==$cat_id):
           $m=$m+1;
           $prot[$m]=array('id'=>$gusers[$i]['id'],'login'=>$gusers[$i]['login'],
                          'description'=>$gusers[$i]['description'],self::perm_struc=>$cat_id);
           endif;
      endfor;
   return $prot;
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
   #  used constants:
   #      self::guardian
   #      self::visitor
   #      self::perm_struc
   #
   # --- guardian users who protect access to a category that contains the article
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
        if($art_gusers[$i]['description']==self::guardian)
          $prot=array('id'=>$art_gusers[$i]['id'],'protect'=>1);
     endif;
   #
   # --- protected area, Redaxo editor signed in
   if(count($prot)<=0):
     if(count($rexeditor)>0):
       $redid=$rexeditor['id'];
       if($redid>=2):
         $catid=$rexeditor[self::perm_struc];
         for($i=1;$i<=count($art_gusers);$i=$i+1):
            $cid=$art_gusers[$i][self::perm_struc];
            for($k=0;$k<count($catid);$k=$k+1)
               if($catid[$k]==$cid) $prot=array('id'=>$redid,'protect'=>0);
            endfor;
         endif;
       endif;
     endif;
   #
   # --- protected category, visitor authenticated
   if(count($prot)<=0):
     $visitor=self::session_get(self::visitor);
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
        if($art_gusers[$i]['description']==self::guardian) continue;
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
   #      >1      = Id of the guarding user who protects the access to the category
   #                that contains the current article
   #              access denied because
   #              - the article is located in one of the protected categories and
   #              - the visitor has not authenticated via sign-in page
   #                (giving login and password of one of the guardian users
   #                protecting a category containing the article) or
   #   used functions:
   #      self::guardian_users()
   #      self::rex_editor()
   #      self::protected_or_prohibited_intern($art_id,$gusers,$rexeditor)
   #   Exemplary snippet of the page template, displays an error message or
   #   presents a sign-in form instead of the article content:
   #       . . .
   #      $uid=access_control::protected_or_prohibited();
   #       . . .
   #      if(intval($uid)<=0) . . . // navigation
   #       . . .
   #      if(intval($uid)>0):
   #        echo access_control::access_allowed($uid); // link to a sign-in page
   #        else:
   #        echo $this->getArticle(); // article content
   #        endif;
   #       . . .
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
#
# ----- protecting media categories functions -------------------------
public static function top_parent_media_category($mediatype,$file) {
   #   Returns the Id of top parent_media_category.
   #   $mediatype         given media type (identify files in media subfolders)
   #   $file              given media file (relative file name: /media/$file)
   #   used constants:
   #      self::cat_marker
   #
   $topmedcatid=0;
   #
   $media=rex_media::get($file);
   if($media!=null):
     #
     # --- file is rex media object
     #     determine the top parent media category
     $medcat=$media->getCategory();
     if($medcat!=NULL):
       $medpath=$medcat->getPathAsArray();
       if(count($medpath)>0):
         $topmedcatid=$medpath[0];
         else:
         $topmedcatid=$medcat->getId();
         endif;
       endif;
     else:   
     #
     # --- file is located in a subfolder of the media folder
     $topdir=self::cat_marker.$mediatype;
     #     top media category already defined?
     $sql=rex_sql::factory();
     $table=rex::getTablePrefix().'media_category';
     $query='SELECT id FROM '.$table.' WHERE parent_id=0 AND name=\''.$topdir.'\'';
     $sql->setQuery($query);
     if($sql->getRows()>0) $topmedcatid=$sql->getValue('id');
     endif;
   return $topmedcatid;
   }
public static function media_guardian_users($mediatype,$file,$gusers) {
   #   Determines whether access to a media file is protected by guardian users.
   #   Returns the Ids of the guardian users in a numbered array (numbering from 1).
   #   $mediatype         given media type (identify files in media subfolders)
   #   $file              given media file (relative file name: /media/$file)
   #   $gusers            array of guarding users
   #   used functions:
   #      self::top_parent_media_category($mediatype,$file)
   #   used constants:
   #      self::perm_media
   #
   $tpmcatid=self::top_parent_media_category($mediatype,$file);
   if($tpmcatid<=0) return array();
   #
   $m=0;
   $uid=array();
   for($i=1;$i<=count($gusers);$i=$i+1):
      $user=$gusers[$i];
      $userid=$user['id'];
      $mid=$user[self::perm_media];
      for($k=0;$k<count($mid);$k=$k+1)
        if($mid[$k]==$tpmcatid):
           $m=$m+1;
           $uid[$m]=$userid;
           endif;
     endfor;
    return $uid;
   }
public static function control_file($mediatype,$file) {
   #   Decides whether a media file is allowed to be displayed. If not,
   #   $_GET['rex_media_type'] is set to 'default' and
   #   $_GET['rex_media_file'] is set to the absolute URL path of an error file.
   #   $mediatype         given media type (identify files in media subfolders)
   #   $file              given media file (relative file name: /media/$file)
   #   used functions:
   #      self::guardian_users()
   #      self::rex_editor()
   #      self::media_guardian_users($mediatype,$file,$gusers)
   #      self::top_parent_media_category($mediatype,$file)
   #      self::session_get($type)
   #      self::get_rex_user($uid)
   #   used constants:
   #      self::this_addon
   #      self::visitor
   #      self::perm_media
   #
   # --- media file protected?
   $gusers=self::guardian_users();
   $protid=self::media_guardian_users($mediatype,$file,$gusers);
   if(count($protid)>0):
     $protected=TRUE;
     else:
     $protected=FALSE;;
     endif;
   #
   # --- access allowed?
   $rexeditor=self::rex_editor();
   $allowed=TRUE;
   if($protected):
     #     site administrator or Redaxo editor has signed in?
     $son=FALSE;
     if($rexeditor['id']==1) $son=TRUE;   // site administrator
     if($rexeditor['id']>=2):
       $tpmcatid=self::top_parent_media_category($mediatype,$file);
       $catid=$rexeditor[self::perm_media];
       for($k=0;$k<count($catid);$k=$k+1)
          if($catid[$k]==$tpmcatid) $son=TRUE;   // Redaxo editor
       endif;
     if(!$son):
       #     visitor has signed in?
       $sesuser=self::session_get(self::visitor);
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
   # --- re-direct to an error file if access is not allowed
   if(!$allowed):
     $errfile=rex_path::addonAssets(self::this_addon,'protected.gif');
     $errfile=substr($errfile,strlen(rex_path::base())-1);
     $manager=rex_media_manager::create('','..'.$errfile);
     $manager->sendMedia();
     endif;
   }
#
# ----- sign-in page --------------------------------------------------
public static function write_signin_page() {
   #   Write the source code for the sign-in page and returns whether the file
   #   exists.
   #   Used in install.php, only. ##########
   #
   $buffer='<?php
#
# --- use REDAXO classes outside of redaxo context
#     https://friendsofredaxo.github.io/tricks/snippets/require_redaxo
$REX["REDAXO"]        =false;
$REX["HTDOCS_PATH"]   =__DIR__."/";
$REX["BACKEND_FOLDER"]="redaxo";
$REX["LOAD_PAGE"]     =false;
$base=$REX["HTDOCS_PATH"].$REX["BACKEND_FOLDER"];
// include REDAXO
require $REX["HTDOCS_PATH"].$REX["BACKEND_FOLDER"]."/src/core/boot.php";
// include addons (optional)
require $REX["HTDOCS_PATH"].$REX["BACKEND_FOLDER"]."/src/core/boot.php";
//
// add this code for multiple languages for a certain add-on
   $addon="access_control";
$langdir=$REX["HTDOCS_PATH"].$REX["BACKEND_FOLDER"]."/src/addons/".$addon."/lang/";
$langs=rex_clang::getAll();
$locales=rex_i18n::getLocales();
for($m=1;$m<=count($langs);$m=$m+1):
   $code=$langs[$m]->getCode();
   $locale="";
   for($i=0;$i<count($locales);$i=$i+1)
      if(substr($locales[$i],0,2)==$code):
        $locale=$code."_".substr($locales[$i],3);
        break;
        endif;
   if(empty($locale)) $locale=rex_i18n::getLocale();
   rex_i18n::setLocale($locale);
   $lines=file($langdir.$locale.".lang");
   for($i=0;$i<count($lines);$i=$i+1):
      $arr=explode("=",$lines[$i]);
      if(count($arr)>1) rex_i18n::addMsg(trim($arr[0]),trim($arr[1]));
      endfor;
   endfor;
#
# --- print sign-in page
$addon::signin_page();
?>';
   $buffer=str_replace('"','\'',$buffer);
   #
   # --- write the file
   $file=rex_path::base().self::signin_page;
   $handle=fopen($file,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   return file_exists($file);
   }
public static function signin_page() {
   #   Displays a page containing an authentication form for a guardian user.
   #      Input values:
   #   $_GET['locale']    locale of the current article ('de_de' or 'en_gb') or
   #                      locale of the sign-in page (if empty)
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
   #      self::get_locale()
   #      self::guardian_users()
   #      self::get_rex_user($uid)
   #      self::session_get($type)
   #      self::session_end($user)
   #      self::session_set($type,$user)
   #   used constants:
   #      self::this_addon
   #      self::guardian
   #      self::visitor
   #
   # --- start of 'template'
   $path=rex_path::assets();
   $arr=explode(DIRECTORY_SEPARATOR,$path);
   $nr=count($arr)-2;
   $path=DIRECTORY_SEPARATOR.$arr[$nr].DIRECTORY_SEPARATOR;
   $path1=$path.'addons'.DIRECTORY_SEPARATOR.self::this_addon;
   $path2=$path.'addons'.DIRECTORY_SEPARATOR.'be_style'.DIRECTORY_SEPARATOR.'css';
   echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-in</title>
    <link rel="stylesheet" href="'.$path1.DIRECTORY_SEPARATOR.self::this_addon.'.css">
    <link rel="stylesheet" href="'.$path2.DIRECTORY_SEPARATOR.'styles.css">
</head>
<body>';   
   #
   # --- setLocale
   if(empty($_GET['locale'])):
     $locale=self::get_locale();
     else:
     $locale=$_GET['locale'];
     endif;
   rex_i18n::setLocale($locale);
   #
   # --- get guardian user's Id, if empty switch to ALL guardian users Ids
   $uidstr='';
   if(!empty($_GET['uid'])) $uidstr=$_GET['uid'];
   if(empty($uidstr)):
     $gusers=self::guardian_users();
     if(count($gusers)<=0 or (count($gusers)==1 and $gusers[1]['description']==self::guardian)):
       echo '
<p class="ac_error">'.rex_i18n::rawMsg('ac_signin_no_protector').'</p>';
       return;
       endif;
     for($i=1;$i<=count($gusers);$i=$i+1)
        if($gusers[$i]['description']!=self::guardian) $uidstr=$uidstr.','.$gusers[$i]['id'];
     if(strlen($uidstr)>=1) $uidstr=substr($uidstr,1);
     endif;
   #
   $logoff='';
   $action='';
   if(!empty($_POST['action'])) $action=$_POST['action'];
   #
   # --- anyone already logged in?
   $visitor=self::session_get(self::visitor);
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
     $sesuser=self::session_get(self::visitor);
     self::session_end($sesuser);
     endif;
   #
   # --- analysing the input values
   $error='';
   #     empty login?
   if(empty($login)) $error=rex_i18n::rawMsg('ac_signin_in_username_pwd');
   #     wrong login?
   if(empty($error)):
     $num=-1;
     for($i=0;$i<count($uid);$i=$i+1)
        if($login==$lognam[$i]):
          $num=$i;
          break;
          endif;
     if($num<0) $error=rex_i18n::rawMsg('ac_signin_wrong_username');
     endif;
   #     empty password?
   if(empty($error) and empty($passwd)
      and empty($logoff))   // generating a logoff page
     $error=rex_i18n::rawMsg('ac_signin_in_pwd');
   #     wrong password?
   if(empty($error)
      and empty($logoff)):   // generating a logoff page
     $ok=rex_login::passwordVerify($passwd,$password[$num]);
     if(!$ok) $error=rex_i18n::rawMsg('ac_signin_wrong_pwd');
     endif;
   #
   # --- display the form
   echo '
<form method="post">
<table class="ac_table">';
   if(!empty($error)):
     #     sign-in form
     echo '
    <tr><td class="ac_td1">
            '.rex_i18n::rawMsg('ac_signin_username').':&nbsp;</td>
        <td class="ac_td2">
            <input type="text" name="login" value="'.$login.'" class="form-control"></td></tr>
    <tr><td class="ac_td1">
            '.rex_i18n::rawMsg('ac_signin_pwd').':</td>
        <td class="ac_td2">
            <input type="password" name="passwd" value="'.$passwd.'" class="form-control"></td></tr>
    <tr><td colspan=2" class="ac_td1">
            <p class="ac_error">'.$error.'</p></td>
    <tr><td></td>
        <td class="ac_td2">
            <button type="submit" name="action" class="btn btn-apply" value="">
            '.rex_i18n::rawMsg('ac_signin_button_in').'</button></td></tr>';
     #     return value
     $uidret=$uidstr;
     else:
     #     set session user
     $gusers=self::guardian_users();
     $sesuser=array();
     for($i=1;$i<=count($gusers);$i=$i+1)
        if($gusers[$i]['id']==$uid[$num]) $sesuser=$gusers[$i];
     self::session_set(self::visitor,$sesuser);
     #     sign-off form
     $success=rex_i18n::rawMsg('ac_signin_user').' \''.$lognam[$num].'\' '.
              rex_i18n::rawMsg('ac_signin_authenticated');
     echo '
    <tr><td class="ac_td1">
            <input type="hidden" name="login" value="">
            <input type="hidden" name="passwd" value=""></td>
        <td class="ac_td2">
            <h4 class="ac_success">'.$success.'.</h4>
            <p class="ac_success">'.rex_i18n::rawMsg('ac_signin_reload').'</p>
            <p><br>'.rex_i18n::rawMsg('ac_signin_logoff').' &nbsp;
            <button type="submit" name="action" class="btn btn-apply"
                    value="'.rex_i18n::rawMsg('ac_signin_val_off').'">
            '.rex_i18n::rawMsg('ac_signin_button_off').'</button> &nbsp; ?</p></td></tr>';
     #     return value
     $uidret=$uid[$num];
     endif; 
   echo '
</table>
</form>';
   #
   # --- end of 'template'
   echo '
</body>
</html>';
   return $uidret;
   }
}
?>