<table class="wiki">
    <tr>
        <td style="width: 100%; vertical-align: top">{$content}</td>
        {if $sidebar}
            <td style="vertical-align: top" class="sidebar"><div id="sticker">{$sidebar}</div></td>
        {/if}
    </tr>
</table>
<script type="text/javascript">
    {literal}
    $(".senseid").each(function () {
                var a;
                $(this).hover(function () {
                            $(a).toggle();
                            if (!a) {
                                a = document.createElement("a");
                                a.href = "#" + this.id;
                                $(a).text("#").css({position: 'absolute', zIndex: 5, marginLeft: '-5px'});
                                $(this).prepend(a);
                            }
                        }
                )
            }
    );

    $(document).ready(function () {
        var s = $("#sticker");
        var pos = s.position();
        $(window).scroll(function () {
            var windowpos = $(window).scrollTop();
            if (windowpos >= pos.top) {
                s.addClass("stick");
            } else {
                s.removeClass("stick");
            }
        });
    });

    {/literal}
</script>