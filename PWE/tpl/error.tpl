{PWE->getStaticHref assign="IMG_HREF"}
{capture assign=text}
    {$trace}
    {$inner}
{/capture}
<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
        <title>{$code} {$code_desc}</title>
    </head>

    <body>
        <h1>{$code} {$code_desc}</h1>
    <big>{$message}</big><br/>
    <br/>
    {if $trace}
        <fieldset>
            <legend>Full Exception:</legend>
            <textarea style="width: 100%;" cols="100" rows="30" readonly="readonly">{$text}</textarea>
        </fieldset>
    {else}
        <i>That's all we have to say...</i>
    {/if}
    <hr/>
    <table style='float:right;' border='0' cellspacing='0' cellpadding='0'>
        <tr>
            <td style='padding: 5px;'><big><b title='Mathematical sign, means "For All"'>&forall;</b></big></td>
    <td align='right'>
        <small> Powered by 
            <a title='Pluggable Web Engine' href='http://code.google.com/p/pwe-cmf/'><b>P&nbsp;W&nbsp;E</b></a>
        </small>
    </td>
    <td style='padding: 5px;'><a href='http://code.google.com/p/pwe-cmf/'><img style='margin-bottom: 3px; border: none;' src='{$IMG_HREF}/design/pwe_logo_small.gif' title='Pluggable Web Engine' alt='PWE' /></a></td>
</tr>
</table>
</body>
</html>