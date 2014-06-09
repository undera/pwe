<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
{assign var=i value='!i'}{assign var=a value='!a'}{assign var=p value='!p'}{assign var=c value='!c'}
{PWE->getStaticHref assign="IMG_HREF"}
{URL->getFullCount assign=urlFullCount}
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
{block name="htmlHead"}
    <head>
        {block name="head"}
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <title>
                {block name="title"}
                    {PWE->getCurrentModuleInstance assign="module"}
                    {if $module|is_a:'PWE\Modules\TitleGenerator'}
                        {$module->generateTitle() assign="title"}
                        {$title|default:$node.$i.title}
                    {else}
                        {$node.$i.title|default:$node.$a.link}
                    {/if}
                {/block}
            </title>
            <meta name="keywords" content="{$node.$i.keywords|default:$node.$i.keywords}"/>
            <meta name="description" content="{$node.$i.description|default:$node.$i.description}"/>
            {if false && $smarty.server.SERVER_ADDR==$smarty.server.REMOTE_ADDR}
                <style type='text/css'>
                    {include file='/src/pwe/design/styles.css'}
                </style>
            {else}
                <link rel="stylesheet" href="{$IMG_HREF}/design/styles.css"/>
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
                <span style="font-weight: bold;"><a
                            href="{$path}{$root.$a.link}/">{$root.$a.title|default:$root.$a.link}</a></span>
            {else}
                <span style="font-weight: bold;"><a href="{$path}">{$root.$a.title|default:$root.$a.link}</a></span>
            {/if}
            {if $smarty.capture.breadcrumbs|strlen}&gt;{/if}
            {$smarty.capture.breadcrumbs}
        {else}
            <span style="font-weight: bold;"><a class="hl"
                                                href="{$path}{$root.$a.link}/">{$root.$a.title|default:$root.$a.link}</a></span>
            {$smarty.capture.breadcrumbs}
        {/if}
    {/capture}
    {assign var=root value=$root.$p}
{/while}

<tr>
    <td style="background-color: white; font-size: 0.8em;">
        <table class="breadcrumbs" style="width: 100%; background-color: white; margin:0;padding:0;" cellspacing="0"
               cellpadding="0">
            <tr>
                {if $smarty.capture.breadcrumbs}
                    <!-- POSITION INFO -->
                    <td style='width: 100%; padding: 2px 2px 3px 4px;'>
                        {block name="breadcrumbs"}
                            {$smarty.capture.breadcrumbs}
                            {PWE->getCurrentModuleInstance assign="module"}
                            {if $module|is_a:'PWE\Modules\BreadcrumbsGenerator'}
                                {$module->generateBreadcrumbs() assign=bcrumbs}

                                {foreach $bcrumbs as $item}
                                    {if $item.selected}
                                        &gt;
                                        <b><a class="hl" href="{$item.$a.link}">{$item.$a.title}</a></b>
                                    {else}
                                        &gt;
                                        <b><a href="{$item.$a.link}">{$item.$a.title}</a></b>
                                    {/if}
                                {/foreach}
                            {/if}
                        {/block}
                    </td>
                    <!-- /POSITION INFO -->
                {/if}

                {block name="login_logout"}
                    {AUTH->getUserID assign=isAuth}
                    {if $isAuth}
                        <td style="padding: 0 4px; white-space: nowrap;">
                            <img alt='' style='vertical-align: text-bottom;' src='{$IMG_HREF}/design/user_icon.png'/>
                            <span style="font-weight: bold;">{AUTH->getUserName}</span>
                        </td>
                        {AUTH->getLevelsUpToAuthNode assign=levels}
                        <td style="padding: 0 4px; white-space: nowrap;">
                            <a href='{'../'|str_repeat:$levels}logout/'>Logout</a>
                        </td>
                    {/if}
                {/block}
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
                                    alt="{$root.$a.title}" title='{$root.$a.title}' style="border: none;"/></a>
                    </td>
                    <!-- /LOGO -->

                    <!-- PAGE TITLE -->
                    <td class='header_title'>
                        {block name="header_title"}
                            {PWE->getCurrentModuleInstance assign="module"}
                            {if $module|is_a:'PWE\Modules\TitleGenerator'}
                                {$module->generateTitle() assign="title"}
                                {$title|default:$node.$i.title}
                            {else}
                                {$node.$i.title|default:$node.$a.link}
                            {/if}
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
        <td class='menu1' style="background: repeat-x center left;">
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
                                            <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'><img
                                                        src="{$item1.$a.menu_icon}" alt=''
                                                        style="vertical-align: bottom;"/></a>
                                        {/if}
                                        <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'>{$item1.$a.title|default:$item1.$a.link}</a>
                                    </td>
                                    <!--/item1h-->
                                {else}
                                    <!--item1-->
                                    <td style="white-space: nowrap;">
                                        {if $item1.$a.menu_icon}
                                            <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'><img
                                                        src="{$item1.$a.menu_icon}" alt=''
                                                        style="vertical-align: bottom;"/></a>
                                        {/if}
                                        <a href='{'../'|str_repeat:$upper_repeats}{$item1.$a.link}/'>{$item1.$a.title|default:$item1.$a.link}</a>
                                    </td>
                                    <!--/item1-->
                                {/if}
                            {/if}
                        {/foreach}
                        <td style="width: 100%" align="right">
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
                    <td style="white-space: nowrap;" valign='top' class='root' id="leftSidebar">
                        {block 'menu2'}
                            <span class='menu2' style="font-weight: bold;">
                                {math assign=upper_repeats equation='x-2' x=$urlFullCount}
                                {foreach $level2 as $item2}
                                    {if $item2.$a.menu}
                                        {if $item2.selected}
                                            <!--item2h-->
                                            -
                                            <a class="hl"
                                               href='{'../'|str_repeat:$upper_repeats}{$item2.$a.link}/'>{$item2.$a.title|default:$item2.$a.link}</a>
                                            <br/>





                                                                                                                                                                                                                                                                                            <!--/item2h-->
                                        {else}
                                            <!--item2-->





                                            <a href='{'../'|str_repeat:$upper_repeats}{$item2.$a.link}/'>{$item2.$a.title|default:$item2.$a.link}</a>
                                            <br/>
                                            <!--/item2-->
                                        {/if}
                                    {/if}
                                {/foreach}
                            </span>
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
                                                <td><a class="hl"
                                                       href='{'../'|str_repeat:$upper_repeats}{$item3.$a.link}/'>{$item3.$a.title|default:$item3.$a.link}</a>
                                                </td>
                                                <!--/item3h-->
                                            {else}
                                                <!--item3-->
                                                <td>
                                                    <a href='{'../'|str_repeat:$upper_repeats}{$item3.$a.link}/'>{$item3.$a.title|default:$item3.$a.link}</a>
                                                </td>
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
            <table style="width: 100%; border: 0; padding: 0; border-collapse: collapse;">
                <tr>
                    {block name="footer_left"}
                        <td class="footer-left"></td>
                    {/block}
                    {block name="footer_center"}
                        <td class="footer-center"></td>
                    {/block}
                    {block name="footer_right"}
                        <td class="footer-right">
                            <table style='float:right;' border='0' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td style="padding: 5px; font-size: large; font-weight: bold;"
                                        title='Mathematical sign, means "For All"'>&forall;</td>
                                    <td align='right' style="font-size: 80%;">
                                        Powered by
                                        <a title='Pluggable Web Engine' href='https://github.com/undera/pwe'
                                           style="font-weight: bold;">P&nbsp;W&nbsp;E</a>
                                    </td>
                                    <td style='padding: 5px;'><a href='https://github.com/undera/pwe'><img
                                                    style='margin-bottom: 3px; border: none;'
                                                    src='{$IMG_HREF}/design/pwe_logo_small.gif'
                                                    title='Pluggable Web Engine'
                                                    alt='PWE'/></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    {/block}
                </tr>
            </table>
        {/block}
    </td>
</tr>
<!-- /FOOTER -->
</table>
</body>
</html>