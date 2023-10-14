<?php
/*
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Oktober 2023
 */
$sta='style="margin-left:20px;"';
$stb='style="list-style:circle; margin-bottom:0;"';
$stc='style="background-color:inherit; margin:0; padding:0, border:none;"';
#
echo '<h4>'
.rex_i18n::rawMsg("ac_details_head0").'</h4>
<div>'
.rex_i18n::rawMsg("ac_details_par0").'</div>

<div><br/><b>'.
rex_i18n::rawMsg("ac_details_head1").'</b></div>
<div class="ac_indent">'
.rex_i18n::rawMsg("ac_details_par1a").'</div>
<ul '.$stb.'>
    <li '.$sta.'>'.rex_i18n::rawMsg("ac_details_par1b").'</li>
    <li '.$sta.'>'.rex_i18n::rawMsg("ac_details_par1c").'</li>
    <li '.$sta.'>'.rex_i18n::rawMsg("ac_details_par1d").'</li>
    <li '.$sta.'>'.rex_i18n::rawMsg("ac_details_par1e").'</li>
</ul>
<div class="ac_indent">'
.rex_i18n::rawMsg("ac_details_par1f").'</div>

<div><br/><b>'
.rex_i18n::rawMsg("ac_details_head2").'</b></div>
<div class="ac_indent">'
.rex_i18n::rawMsg("ac_details_par2a").'
<table '.$stc.'>
    <tr valign="top">
        <td class="ac_indent">=0:</td>
        <td class="ac_indent">'
            .rex_i18n::rawMsg("ac_details_par2b").'</td></tr>
    <tr valign="top">
        <td class="ac_indent">=1:</td>
        <td class="ac_indent">'
            .rex_i18n::rawMsg("ac_details_par2c").'</td></tr>
    <tr valign="top">
        <td class="ac_indent">&gt;1:</td>
        <td class="ac_indent">'           
            .rex_i18n::rawMsg("ac_details_par2d").'</td></tr>
</table>'
.rex_i18n::rawMsg("ac_details_par2e").' \'<tt>'.access_control::signin_page.'</tt>\').<pre>
$uid=access_control::protected_or_prohibited();
 . . .
if(intval($uid)<=0) . . . // display navigation
 . . .
if(intval($uid)>0):
  echo access_control::access_allowed($uid); // link to the sign-in page
  else:
  echo $this->getArticle(); // article content
  endif;</pre>'
  .rex_i18n::rawMsg("ac_details_par2f").'</div>

<div><br/><b>'
.rex_i18n::rawMsg("ac_details_head3").'</b></div>
<div class="ac_indent">'
.rex_i18n::rawMsg("ac_details_par3").'
<pre>
 RewriteRule  ^media/(.*)$  /index.php?rex_media_type=default&amp;rex_media_file=$1</pre>
</div>

<div>&nbsp;</div>';
?>