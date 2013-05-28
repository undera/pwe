<div id="chartdiv{$key}" style="border: 1px dotted silver; width:{$width|default:'600px;'}; height:{$height|default:'400px;'}; background-color:#FFFFFF"></div>

<script type="text/javascript">
    var chart{$key};
    var dataProvider{$key};

    {block name="createChart"}
        chart{$key} = new AmCharts.AmSerialChart();
        chart{$key}.categoryField = "x";
        chart{$key}.pathToImages="{$IMG_HREF}/amcharts/images/";
    
        var graph = new AmCharts.AmGraph();
        graph.title="{$key}";
        graph.valueField = "y1";
        chart{$key}.addGraph(graph);
    {/block}
    
        var legend = new AmCharts.AmLegend();
        legend.labelText='[[title]]';
        chart{$key}.addLegend(legend);
        chart{$key}.write('chartdiv{$key}');

        loadCSV(chart{$key}, "{$dataURL}");
</script>
