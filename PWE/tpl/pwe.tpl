
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
{assign var=i value='!i'}{assign var=a value='!a'}{assign var=p value='!p'}{assign var=c value='!c'}
{PWE->getStaticHref assign="IMG_HREF"}
{URL->getFullCount assign=urlFullCount}
<html  xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    {block name="htmlHead"}
        <head>
            {block name="head"}
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>{block name="title"}{$node.$i.title|default:$node.$a.link}{/block}</title>
                <meta name="keywords" content="{$node.$i.keywords|default:$node.$i.keywords}" />
                <meta name="description" content="{$node.$i.description|default:$node.$i.description}" />
                {if false && $smarty.server.SERVER_ADDR==$smarty.server.REMOTE_ADDR}
                    <style type='text/css'>
                        {include file=$smarty.server.SCRIPT_FILENAME|dirname|cat:'/src/pwe/design/styles.css'}
                    </style>
                {else}
                    <link rel="stylesheet" href="{$IMG_HREF}/design/styles.css" />
                {/if}
            {/block}
        </head>
    {/block}
    {* {flush} sometimes it causes problems with headers already sent*}
    <body>
        <table border='0' width="100%" cellspacing='0' cellpadding='0'>
            {URL->getParamsCount assign=paramsCount}
            {URL->getMatchedCount assign=matchCount}
            {assign var=root value=$node}
            {assign var=path value='../'|str_repeat:$paramsCount}
            {while $root}
                {if $root.$a.link}
                    {assign var=path value='../'|cat:$path}
                {/if}

                {capture name=breadcrumbs}
                    {if $path != '../' || !$root.$p}
                        {if $root.$a.link}
                            <b><a href="{$path}{$root.$a.link}/">{$root.$a.title|default:$root.$a.link}</a></b>
                            {else}
                            <b><a href="{$path}">{$root.$a.title|default:$root.$a.link}</a></b>
                            {/if}
                        {if $smarty.capture.breadcrumbs|strlen}&gt;{/if} 
                    {$smarty.capture.breadcrumbs}
                {else}
                    <b><a class="hl" href="{$path}{$root.$a.link}/">{$root.$a.title|default:$root.$a.link}</a></b> {$smarty.capture.breadcrumbs}
                    {/if}
                {/capture}
                {assign var=root value=$root.$p}
            {/while}

        <tr>
            <td style="background-color: white; font-size: 0.8em;">
                <table class="breadcrumbs" style="width: 100%; background-color: white; margin:0px;padding:0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        {if $smarty.capture.breadcrumbs}
                            <!-- POSITION INFO -->
                            <td style='width: 100%; padding: 2px 2px 3px 4px;'>
                            {block name="breadcrumbs"}{$smarty.capture.breadcrumbs}{/block}
                        </td>
                        <!-- /POSITION INFO -->
                    {/if}

                    {AUTH->getUserID assign=isAuth}
                    {if $isAuth}
                        <td style="padding: 0px 4px; white-space: nowrap;">
                            <img alt='' style='vertical-align: text-bottom;' src='{$IMG_HREF}/design/user_icon.png' />
                            <b>{AUTH->getUserName}</b>
                        </td>
                        {AUTH->getLevelsUpToAuthNode assign=levels}
                        <td style="padding: 0px 4px; white-space: nowrap;">
                            <a href='{'../'|str_repeat:$levels}logout/'>Logout</a>
                        </td>
                    {/if}
                </tr>
            </table>
        </td>
    </tr>

    <!-- HEADER -->
    <tr>
        <td id="toppanel">
            {block name="header"}
                <table border='0' width="100%" cellspacing='0' cellpadding='0'>
                    <tr>
                        <!-- LOGO -->
                        {* FIXME: params break this link *}
                        <td class="logo"><a href="{'../'|str_repeat:$urlFullCount}"><img
                                    {if $node.$i.custom_logo}
                                        src="{$node.$i.custom_logo}"
                                    {else}
                                        src="{$IMG_HREF}/design/logo.gif"
                                    {/if}
                                    alt="{$root.$a.title}" title='{$root.$a.title}' style="border: none;" /></a>
                        </td>
                        <!-- /LOGO -->

                        <!-- PAGE TITLE -->
                        <td class='header_title'>
                            {block name="header_title"}
                                {$node.$i.title|default:$node.$a.link}
                            {/block}
                        </td>
                        <!-- /PAGE TITLE -->

                        <td class="header_right">
                            {block name="header_right"}
                            {/block}
                        </td>
                    </tr>
                </table>
            {/block}
        </td>
    </tr>
    <!-- /HEADER -->

    {PWE->getStructLevel level=1 assign=level1}
    {if $level1}
        <!--menu1-->
        <tr>
            <td class='menu1' style="background-repeat: repeat-x; background-position: center left;">
                {block name="menu1"}
                    <table border='0' width="100%" cellspacing='0' cellpadding='0'>
                        <tr>
                            {math assign=upper_repeats equation='x-1' x=$urlFullCount}
                            {foreach $level1 as $item1}
                                {if $item1.$a.menu}
                                    {if $item1.selected}
                                        <!--item1h-->
                                        <td style="white-space: nowrap;" class='hl'>
                                            {if $item1.$a.menu_icon}
                                                <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'><img src="{$item1.$a.menu_icon}" alt=''  style="vertical-align: bottom;"/></a>
                                            {/if}
                                            <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'>{$item1.$a.title|default:$item1.$a.link}</a>
                                        </td>
                                        <!--/item1h-->
                                    {else}
                                        <!--item1-->
                                        <td style="white-space: nowrap;">
                                            {if $item1.$a.menu_icon}
                                                <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'><img src="{$item1.$a.menu_icon}" alt='' style="vertical-align: bottom;"/></a>
                                            {/if}
                                            <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'>{$item1.$a.title|default:$item1.$a.link}</a>
                                        </td>
                                        <!--/item1-->
                                    {/if}
                                {/if}
                            {/foreach}
                            <td style="width: 100%" align="right">
                                <small>
                                    <a id='minimizelink' style="text-decoration:none;" title="Collapse" href="#"
                                       onclick="minimizeHeader();"
                                       >&nbsp;&nbsp;&uarr;&nbsp;&nbsp;</a>
                                    <a id='restorelink' style="text-decoration:none; display:none;" title="Expand" href="#"
                                       onclick="restoreHeader();"
                                       >&nbsp;&nbsp;&darr;&nbsp;&nbsp;</a>
                                </small>
                                <script type="text/javascript">
                                           function SetCookie(cookieName, cookieValue, nDays) {
                                               var today = new Date();
                                               var expire = new Date();
                                               if (nDays == null || nDays == 0)
                                                   nDays = 1;
                                               expire.setTime(today.getTime() + 3600000 * 24 * nDays);
                                               document.cookie = cookieName + "=" + escape(cookieValue)
                                                       + ";expires=" + expire.toGMTString();
                                           }
                                           //
                                           function ReadCookie(cookieName) {
                                               var theCookie = " " + document.cookie;
                                               var ind = theCookie.indexOf(" " + cookieName + "=");
                                               if (ind == -1)
                                                   ind = theCookie.indexOf(";" + cookieName + "=");
                                               if (ind == -1 || cookieName == "")
                                                   return "";
                                               var ind1 = theCookie.indexOf(";", ind + 1);
                                               if (ind1 == -1)
                                                   ind1 = theCookie.length;
                                               return unescape(theCookie.substring(ind + cookieName.length + 2, ind1));
                                           }

                                           function minimizeHeader() {
                                               document.all['toppanel'].style.display = 'none';
                                               document.all['minimizelink'].style.display = 'none';
                                               document.all['restorelink'].style.display = '';
                                               SetCookie("headerMinimized", 1, 30);
                                           }

                                           function restoreHeader() {
                                               document.all['toppanel'].style.display = '';
                                               document.all['minimizelink'].style.display = '';
                                               document.all['restorelink'].style.display = 'none';
                                               SetCookie("headerMinimized", 1, -30);
                                               //alert("Deleted cookie");
                                           }

                                           if (ReadCookie("headerMinimized")) {
                                               minimizeHeader();
                                           }
                                </script>
                            </td>
                        </tr>
                    </table>
                {/block}
            </td>
        </tr>
        <!--/menu1-->
    {/if}
    <!-- MAIN -->
    <tr>
        <td class='root'>
            <table class='main' border='0' width="100%" cellspacing='5' cellpadding='5'>
                <tr>
                    {PWE->getStructLevel level=2 assign=level2}
                    {if $level2}
                        <!--menu2-->
                        <td  style="white-space: nowrap;" valign='top'  class='root'>
                            {block 'menu2'}
                                <b class='menu2'>
                                    {math assign=upper_repeats equation='x-2' x=$urlFullCount}
                                    {foreach $level2 as $item2}
                                        {if $item2.$a.menu}
                                            {if $item2.selected}
                                                <!--item2h-->
                                                - <a class="hl" href='{'../'|str_repeat:$upper_repeats}{$item2.$a.link}/'>{$item2.$a.title|default:$item2.$a.link}</a><br/>
                                                <!--/item2h-->
                                            {else}
                                                <!--item2-->
                                                <a href='{'../'|str_repeat:$upper_repeats}{$item2.$a.link}/'>{$item2.$a.title|default:$item2.$a.link}</a><br/>
                                                <!--/item2-->
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </b>
                                <br/>
                            {/block}
                        </td>
                        <!--/menu2-->
                    {/if}

                    <!-- CONTENT -->
                    <td style="width: 100%;" valign='top' class='root'>
                        {PWE->getStructLevel level=3 assign=level3}
                        {if $level3}                                
                            {block name='menu3'}
                                <!--menu3-->
                                <table class='menu3' border='0' cellspacing='1' cellpadding='0'>
                                    <tr>
                                        {math assign=upper_repeats equation='x-3' x=$urlFullCount}
                                        {foreach $level3 as $item3}
                                            {if $item3.$a.menu}
                                                {if $item3.selected}
                                                    <!--item3h-->
                                                    <td><a class="hl" href='{'../'|str_repeat:$upper_repeats}{$item3.$a.link}/'>{$item3.$a.title|default:$item3.$a.link}</a></td>
                                                    <!--/item3h-->
                                                {else}
                                                    <!--item3-->
                                                    <td><a href='{'../'|str_repeat:$upper_repeats}{$item3.$a.link}/'>{$item3.$a.title|default:$item3.$a.link}</a></td>
                                                    <!--/item3-->
                                                {/if}
                                            {/if}
                                        {/foreach}
                                    </tr>
                                </table>
                                <!--/menu3-->
                            {/block}
                        {/if}
                        <!--content-->
                        {block name="content"}
                            {PWE->getContent}
                        {/block}
                        <!--/content-->
                        <br/>
                    </td>
                    <!-- /CONTENT -->
                </tr>
            </table>
        </td>
    </tr>
    <!-- /MAIN -->

    <!-- FOOTER -->
    <tr>
        <td class="footer">
            {block name="footer"}
                <table style='float:right;' border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td style='padding: 5px;'><big><b title='Mathematical sign, means "For All"'>&forall;</b></big></td>
                        <td align='right'>
                            <small> Powered by 
                                <a title='Pluggable Web Engine' href='https://github.com/undera/pwe'><b>P&nbsp;W&nbsp;E</b></a>
                            </small>
                        </td>
                        <td style='padding: 5px;'><a href='https://github.com/undera/pwe'><img style='margin-bottom: 3px; border: none;' src='{$IMG_HREF}/design/pwe_logo_small.gif' title='Pluggable Web Engine' alt='PWE' /></a></td>
                    </tr>
                </table>
            {/block}
        </td>
    </tr>
    <!-- /FOOTER -->
</table>
</body>
</html>