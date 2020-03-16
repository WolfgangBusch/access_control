<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2020
*/
echo '<div><b>'.
rex_i18n::msg("access_control_details_head1").'</b></div>
<div class="access_control_indent">'.
rex_i18n::msg("access_control_details_par1").'<pre>
   &lt;?php
   $uid=access_control::login_page();   // if($uid<=0): undefined guardian user
   ?&gt;</pre></div>

<div><br/><b>'.
rex_i18n::msg("access_control_details_head2").'</b></div>
<div class="access_control_indent">'.
rex_i18n::msg("access_control_details_par2").'
<div class="access_control_indent">'.
rex_i18n::msg("access_control_details_par2a").'</div>'.
rex_i18n::msg("access_control_details_par2b").'<pre>
   $uid=access_control::protected_or_forbidden();
   if($uid>0):
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
rex_i18n::msg("access_control_details_head3").'</b></div>
<div class="access_control_indent"><pre>
   RewriteRule  ^media/(.*)  index.php?auth_file=$1</pre>'.
rex_i18n::msg("access_control_details_par3").'
</div>
<div>&nbsp;</div>';
?>