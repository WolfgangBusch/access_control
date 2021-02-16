<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2021
*/
echo '<div><b>'.
rex_i18n::rawMsg("access_control_details_head1").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par1a").'<br/>'
.rex_i18n::rawMsg("access_control_details_par1b").'<br/>'
.rex_i18n::rawMsg("access_control_details_par1c").'<br/>'
.rex_i18n::rawMsg("access_control_details_par1d").'<br/>'
.rex_i18n::rawMsg("access_control_details_par1e").'<br/>'
.rex_i18n::rawMsg("access_control_details_par1f").'</div>

<div><br/><b>'.
rex_i18n::rawMsg("access_control_details_head2").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par2a").'<pre>
 &lt;?php $uid=access_control::login_page(); ?&gt;</pre>'
.rex_i18n::rawMsg("access_control_details_par2b").'<br/>'
.rex_i18n::rawMsg("access_control_details_par2c").'<br/>'
.rex_i18n::rawMsg("access_control_details_par2d").'<br/>'
.rex_i18n::rawMsg("access_control_details_par2e").'<br/>'
.rex_i18n::rawMsg("access_control_details_par2f").'</div>

<div><br/><b>'
.rex_i18n::rawMsg("access_control_details_head3").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par3a").'
<div class="access_control_indent">'
   .rex_i18n::rawMsg("access_control_details_par3b").'<br/>'
   .rex_i18n::rawMsg("access_control_details_par3c").'<br/>'
   .rex_i18n::rawMsg("access_control_details_par3d").'</div>'
.rex_i18n::rawMsg("access_control_details_par3e").'<pre>
 $uid=access_control::protected_or_prohibited();
 if($uid>0):
    if($uid==1):
     echo \'&lt;p&gt;prohibited area, reserved for the site administrator&lt;/p&gt;\';
     else:
     $path=\'/login.html\';   // path to the sign-in page,  t o &nbsp; b e &nbsp; r e p l a c e d &nbsp; individually
     echo \'&lt;p&gt;article in protected area, access requires authentication&lt;/p&gt;\';
     echo \'&lt;p&gt;&lt;a href="\'.$path.\'?uid=\'.$uid.\'"&gt;Sign in&lt;/a&gt;&lt;/p&gt;\';
     endif;
   else:
   echo $this->getArticle(); // article content
   endif;</pre>'
   .rex_i18n::rawMsg("access_control_details_par3f").'</div>

<div><br/><b>'
.rex_i18n::rawMsg("access_control_details_head4").'</b></div>
<div class="access_control_indent"><pre>
 RewriteRule  ^media/(.*)$  index.php?auth_file=$1</pre>'
.rex_i18n::rawMsg("access_control_details_par4").'
</div>
<div>&nbsp;</div>';
?>