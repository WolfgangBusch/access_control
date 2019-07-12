<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juli 2019
*/
echo '<div><b>'.rex_i18n::msg("access_control_details_head1").':</b>
<code>RewriteRule</code></div>
<div class="access_control_indent">'.
rex_i18n::msg("access_control_details_par1a").':<pre>
   /media/filename1
   index.php?rex_img_file=filename2</pre>'.
rex_i18n::msg("access_control_details_par1b").'<pre>
   index.php?auth_file=filename1</pre>'.
rex_i18n::msg("access_control_details_par1c").':<pre>
   RewriteRule ^media/(.*)    index.php?auth_file=$1</pre>
</div>

<div><br/><b>'.rex_i18n::msg("access_control_details_head2").':</b>
<code>access_control::protected_or_forbidden()</code></div>
<div class="access_control_indent">'.
rex_i18n::msg("access_control_details_par2a").':
<div class="access_control_indent">'.
rex_i18n::msg("access_control_details_par2b").'<br/>'.
rex_i18n::msg("access_control_details_par2c").'<br/>'.
rex_i18n::msg("access_control_details_par2d").'</div>'.
rex_i18n::msg("access_control_details_temp").':<pre>
   $rc=access_control::protected_or_forbidden();
   if($rc>0):
     if($rc==1):
       echo "... protected ...";
       else:
       echo "... forbidden ...";
       endif;
     else:
     echo $this->getArticle(); // article content
     endif;</pre>
</div>';
?>