<?php
/*
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2023
 */
$sta='style="margin-left:20px;"';
$stb='style="list-style:circle; margin-bottom:0;"';
$stc='style="background-color:inherit; margin:0; padding:0, border:none;"';
#
echo '<h4>'
.rex_i18n::rawMsg("access_control_details_head0").'</h4>
<div>'
.rex_i18n::rawMsg("access_control_details_par0").'</div>

<div><br/><b>'.
rex_i18n::rawMsg("access_control_details_head1").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par1a").'</div>
<ul '.$stb.'>
    <li '.$sta.'>'.rex_i18n::rawMsg("access_control_details_par1b").'</li>
    <li '.$sta.'>'.rex_i18n::rawMsg("access_control_details_par1c").'</li>
    <li '.$sta.'>'.rex_i18n::rawMsg("access_control_details_par1d").'</li>
    <li '.$sta.'>'.rex_i18n::rawMsg("access_control_details_par1e").'</li>
</ul>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par1f").'</div>

<div><br/><b>'
.rex_i18n::rawMsg("access_control_details_head2").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par2a").'
<table '.$stc.'>
    <tr valign="top">
        <td class="access_control_indent">=0:</td>
        <td class="access_control_indent">'
            .rex_i18n::rawMsg("access_control_details_par2b").'</td></tr>
    <tr valign="top">
        <td class="access_control_indent">=1:</td>
        <td class="access_control_indent">'
            .rex_i18n::rawMsg("access_control_details_par2c").'</td></tr>
    <tr valign="top">
        <td class="access_control_indent">&gt;1:</td>
        <td class="access_control_indent">'           
            .rex_i18n::rawMsg("access_control_details_par2d").'</td></tr>
</table>'
.rex_i18n::rawMsg("access_control_details_par2e").'<pre>
 $uid=access_control::protected_or_prohibited();
 if($uid>0):
    if($uid==1):
     echo \'&lt;p&gt;prohibited area, reserved for the site administrator&lt;/p&gt;\';
     else:
     $path=\'/login.html\';   // path to the sign-in page,  t o &nbsp; b e &nbsp; r e p l a c e d &nbsp; individually
     echo \'&lt;p&gt;article in protected area, access requires authentication&lt;/p&gt;\';
     echo \'&lt;p&gt;&lt;a href="\'.$path.\'?uid=\'.$uid.\'" target="_blank"&gt;Sign in&lt;/a&gt;&lt;/p&gt;\';
     endif;
   else:
   echo $this->getArticle(); // article content
   endif;</pre>'
   .rex_i18n::rawMsg("access_control_details_par2f").'</div>

<div><br/><b>'
.rex_i18n::rawMsg("access_control_details_head3").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par3").'
<pre>
 RewriteRule  ^media/(.*)$  /index.php?rex_media_type=default&amp;rex_media_file=$1</pre>
</div>

<div><br/><b>'.
rex_i18n::rawMsg("access_control_details_head4").'</b></div>
<div class="access_control_indent">'
.rex_i18n::rawMsg("access_control_details_par4a").'<pre>
 &lt;?php $uid=access_control::login_page(); ?&gt;</pre>
<ul '.$stb.'>
    <li>'.rex_i18n::rawMsg("access_control_details_par4b").'</li>
    <li>'.rex_i18n::rawMsg("access_control_details_par4c").'</li>
    <li>'.rex_i18n::rawMsg("access_control_details_par4d").'</li>
</ul>

<div>&nbsp;</div>';
?>