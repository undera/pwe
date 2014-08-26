{assign var=aKey value='!a'}
{if $subpages}
    {if $format=='table'}
        <table border=0 width="100%" cellspacing=0 cellpadding=20>
            <tr>
                {foreach from=$subpages key=iteration item=item name=items}
                {if !$smarty.foreach.items.first && !$smarty.foreach.items.last && !($iteration % $columns)}
            </tr>
            <tr>
                {/if}
                <td><a title="{$item.$aKey.description}"
                       href="{$item.$aKey.link}/">{$item.$aKey.title|default:$item.$aKey.link}</a></td>
                {/foreach}
            </tr>
        </table>
    {else}
        <ul style='font-size: 1.3em;'>
            {foreach from=$subpages item=item}
                {if $item.$aKey.menu}
                    <li><a title="{$item.$aKey.description}"
                           href="{$item.$aKey.link}/">{$item.$aKey.title|default:$item.$aKey.link}</a></li>
                {/if}
            {/foreach}
        </ul>
    {/if}
{else}
    Раздел в состоянии разработки
{/if}