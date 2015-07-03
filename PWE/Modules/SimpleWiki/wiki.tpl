<table class="wiki">
    <tr>
        <td style="width: 100%; vertical-align: top">{$content}</td>
        {if $sidebar}
            <td style="vertical-align: top" class="sidebar">{$sidebar}</td>
        {/if}
    </tr>
</table>
<script type="text/javascript">
    {literal}
    jQuery.fn.visible = function () {
        return this.css('visibility', 'visible');
    };

    jQuery.fn.invisible = function () {
        return this.css('visibility', 'hidden');
    };

    jQuery.fn.visibilityToggle = function () {
        return this.css('visibility', function (i, visibility) {
            return (visibility == 'visible') ? 'hidden' : 'visible';
        });
    };

    for (var level = 1; level <= 6; level++)
        $("h" + level).each(function () {
                    var a;
                    $(this).hover(function () {
                                $(a).visibilityToggle();
                                if (!a) {
                                    a = document.createElement("a");
                                    a.href = "#" + this.id;
                                    $(a).text("#").addClass("anchorLink").css({position: 'absolute', zIndex: 5});
                                    $(this).append(a);
                                }
                            }
                    )
                }
        );
    {/literal}
</script>