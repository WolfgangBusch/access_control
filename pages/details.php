<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2020
*/
echo '<div><b>'.
rex_i18n::rawMsg("access_control_details_head1").'</b></div>
<div class="access_control_indent">
'.rex_i18n::rawMsg("access_control_details_par1a").'
<div class="access_control_indent">
'.rex_i18n::rawMsg("access_control_details_par1b").'</div>
'.rex_i18n::rawMsg("access_control_details_par1c").'<br/>
'.rex_i18n::rawMsg("access_control_details_par1d").'</div>

<div><br/><b>'.
rex_i18n::rawMsg("access_control_details_head2").'</b></div>
<div class="access_control_indent">'.
rex_i18n::rawMsg("access_control_details_par2a").'
<div class="access_control_indent">'.
rex_i18n::rawMsg("access_control_details_par2b").'</div>'.
rex_i18n::rawMsg("access_control_details_par2c").'<pre>
   $uid=access_control::protected_or_forbidden();
   if($uid>0):
     #     $uid = integer (>0) or comma-separated list of integers (>0 each)
     if($uid==1):
       #   forbidden
       else:
       #   protected
       echo \'... &lt;a href="...../login_page.html?uid=$uid"&gt;Sign in&lt;/a&gt; ...\';
       endif;
     else:
     echo $this->getArticle(); // article content
     endif;</pre>
</div>

<div><br/><b>'.
rex_i18n::rawMsg("access_control_details_head3").'</b></div>
<div class="access_control_indent">'.
rex_i18n::rawMsg("access_control_details_par3a").'<pre>
 &lt;?php [$uid=]access_control::login_page(); ?&gt;</pre>'.
rex_i18n::rawMsg("access_control_details_par3b").'</div>

<div><br/><b>'.
rex_i18n::rawMsg("access_control_details_head4").'</b></div>
<div class="access_control_indent"><pre>
   RewriteRule  ^media/(.*)  index.php?auth_file=$1</pre>'.
rex_i18n::rawMsg("access_control_details_par4").'
</div>
<div>&nbsp;</div>';
?>