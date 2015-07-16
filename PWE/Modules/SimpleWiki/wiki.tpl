<table class="wiki">
    <tr>
        <td>{$content}</td>
        {if $sidebar}
            <td class="sidebar">
                <div id="sticker">{$sidebar}</div>
                <div class="stickPlaceholder">&nbsp;</div>
            </td>
        {/if}
    </tr>
</table>
<script type="text/javascript">
    {literal}
    jQuery.fn.visibilityToggle = function () {
        return this.css('visibility', function (i, visibility) {
            return (visibility == 'visible') ? 'hidden' : 'visible';
        });
    };

    for (var level = 1; level <= 6; level++) {
        $("h" + level).each(function () {
                    var a;
                    $(this).hover(function () {
                                $(a).visibilityToggle();
                                if (!a) {
                                    a = document.createElement("a");
                                    a.href = "#" + this.id.toLowerCase();
                                    a.id = this.id.toLowerCase();
                                    $(a).html("&para;").addClass("anchorLink").css({position: 'relative', zIndex: 5});
                                    $(this).append(a);
                                }
                            }
                    )
                }
        );
    }

    $(document).ready(function () {
        var s = $("#sticker");
        var pos = s.position();
        $(window).scroll(function () {
            var windowpos = $(window).scrollTop();
            if (windowpos >= pos.top) {
                s.parent().find(".stickPlaceholder").width(s.width());
                s.addClass("stick");
            } else {
                s.removeClass("stick");
            }
        });
    });

    {/literal}
</script>