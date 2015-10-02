<?php
namespace YaoiTests\PHPUnit\View;

use Yaoi\Rows\Processor;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\View\HighCharts;
use Yaoi\View\HighCharts\Series;
use Yaoi\View\HighCharts\Table;


class ChartTest extends TestCase
{


    public function testSeries()
    {
        //var_export(View_HighCharts_Series::create()->exportOptions());

        $this->assertSame(
            '{"data":[]}',
            json_encode(Series::create()->exportOptions())
        );

// http://www.highcharts.com/demo/arearange-line
        $this->assertSame(
            '{"data":[],"name":"test"}',
            json_encode(Series::create()
                ->setName('test')
                ->exportOptions())
        );


        $this->assertSame(
            '{"data":[[1,2],[2,2],[3,1]],"name":"test"}',
            json_encode(Series::create()
                ->setName('test')
                ->addRow('1', 2)
                ->addRow(2, 2)
                ->addRow(3, '1')
                ->exportOptions())
        );


    }


    public function testRange()
    {
        $result = (string)HighCharts::create()
            ->setContainerSelector('#test')
            ->addSeries(
                Series::create()
                    ->setType(Series::TYPE_AREA_SPLINE_RANGE)
                    ->setId('low1')
                    ->setName('Range ooo!')
                    ->addRangeRow(-1, -2, 0),
                'high1'
            )
            ->addSeries(
                Series::create()
                    ->setId('mid')
                    ->setType(Series::TYPE_SPLINE)
            )
            ->addRow(0, -1, 'low1')
            ->addRow(0, 1, 'high1')
            ->addRow(1, 0, 'low1')
            ->addRow(1, 1, 'high1')
            ->addRow(2, 3, 'low1')
            ->addRow(2, 5, 'high1')
            ->addRow(0, 0.5, 'mid')
            ->addRow(0.5, 0.8, 'mid')
            ->addRow(1, 0.9, 'mid')
            ->addRow(2, 4, 'mid');

        //echo $result;


        $e = <<<'EOD'
<script type="text/javascript" src="http://code.highcharts.com/stock/highstock.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts-more.js"></script>
<script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#test').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[-2,null,null],[0,-1,1],[1,0,1],[2,3,5]],"id":"low1","name":"Range ooo!","type":"areasplinerange"}, false);
    chart.addSeries({"data":[[0,0.5],[0.5,0.8],[1,0.9],[2,4]],"id":"mid","name":"mid","type":"spline"}, false);
    chart.redraw();
})();
</script>
EOD;

        $this->assertStringEqualsCRLF($e, $result);


    }


    public function testAutoId()
    {
        $rows = array();
        $rows [] = array('x' => 1, 'val' => 112, 'serie' => 'serie 1');
        $rows [] = array('x' => 2, 'val' => 114, 'serie' => 'serie 1');
        $rows [] = array('x' => 2, 'val' => 117, 'serie' => 'serie 2');
        $rows [] = array('x' => 3, 'val' => 113, 'serie' => 'serie 2');

        //View_HighCharts_Table::create($rows)->setId('test')->withNamedSeries()->render();
        //return;

        $e = <<<'EOD'
<div id="hc-container-1"></div><script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#hc-container-1').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,112],[2,114]],"id":"serie 1","name":"serie 1"}, false);
    chart.addSeries({"data":[[2,117],[3,113]],"id":"serie 2","name":"serie 2"}, false);
    chart.redraw();
})();
</script>
EOD;
        $this->assertStringEqualsCRLF($e, (string)Table::create($rows)->withNamedSeries());


        $e = <<<'EOD'
<div id="hc-container-2"></div><script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#hc-container-2').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,112],[2,114]],"id":"serie 1","name":"serie 1"}, false);
    chart.addSeries({"data":[[2,117],[3,113]],"id":"serie 2","name":"serie 2"}, false);
    chart.redraw();
})();
</script>
EOD;
        $this->assertStringEqualsCRLF($e, (string)Table::create($rows)->withNamedSeries());

    }


    public function testRegular()
    {
        $rows = array();
        $rows [] = array('x' => 1, 'one' => 23, 'two' => 112);
        $rows [] = array('x' => 2, 'one' => 24, 'two' => 113);
        $rows [] = array('x' => 3, 'one' => 25, 'two' => 114);
        $rows [] = array('x' => 4, 'one' => 21, 'two' => 117);

        //View_HighCharts_Table::create($rows)->setId('test')->render();
        //return;


        $e = <<<'EOD'
<div id="test"></div><script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#test').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,23],[2,24],[3,25],[4,21]],"id":"one","name":"one"}, false);
    chart.addSeries({"data":[[1,112],[2,113],[3,114],[4,117]],"id":"two","name":"two"}, false);
    chart.redraw();
})();
</script>
EOD;
        $this->assertStringEqualsCRLF($e, (string)Table::create($rows)->setId('test'));


        $iterator = new \ArrayIterator($rows);
        $this->assertStringEqualsCRLF($e, (string)Table::create($iterator)->setId('test'));
    }


    public function testNamed()
    {
        $rows = array();
        $rows [] = array('x' => 1, 'val' => 112, 'serie' => 'serie 1');
        $rows [] = array('x' => 2, 'val' => 114, 'serie' => 'serie 1');
        $rows [] = array('x' => 2, 'val' => 117, 'serie' => 'serie 2');
        $rows [] = array('x' => 3, 'val' => 113, 'serie' => 'serie 2');

        //View_HighCharts_Table::create($rows)->setId('test')->withNamedSeries()->render();
        //return;

        $e = <<<'EOD'
<div id="test"></div><script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#test').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,112],[2,114]],"id":"serie 1","name":"serie 1"}, false);
    chart.addSeries({"data":[[2,117],[3,113]],"id":"serie 2","name":"serie 2"}, false);
    chart.redraw();
})();
</script>
EOD;

        $this->assertStringEqualsCRLF($e, (string)Table::create($rows)->setId('test')->withNamedSeries());

        $rows = array();
        $rows [] = array(1, 112, 'serie 1');
        $rows [] = array('2', 114, 'serie 1');
        $rows [] = array('2', 117, 'serie 2');
        $rows [] = array(3, '113', 'serie 2');

        $this->assertStringEqualsCRLF($e, (string)Table::create($rows)->setId('test')->withNamedSeries());

        $iterator = new \ArrayIterator($rows);
        $this->assertStringEqualsCRLF($e, (string)Table::create($iterator)->setId('test')->withNamedSeries());


        $rowsCombined = Processor::create($rows)->combineOffset(2, 1);
        $this->assertStringEqualsCRLF($e, (string)Table::create($rowsCombined)->setId('test'));


    }

    /**
     * @expectedException     \Yaoi\View\Exception
     * @expectedExceptionCode \Yaoi\View\Exception::WRONG_DATA_TYPE
     */
    public function testWrongData()
    {
        Table::create(new \stdClass())->render();
    }


    public function testWithLang()
    {
        $rows = array();
        $rows [] = array('x' => 1, 'val' => 112, 'serie' => 'serie 1');
        $rows [] = array('x' => 2, 'val' => 114, 'serie' => 'serie 1');
        $rows [] = array('x' => 2, 'val' => 117, 'serie' => 'serie 2');
        $rows [] = array('x' => 3, 'val' => 113, 'serie' => 'serie 2');

        //View_HighCharts_Table::create($rows)->setId('test')->withNamedSeries()->render();
        //return;

        $e = <<<'EOD'
<div id="test"></div><script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false},"lang":{"shortMonths":["\u042f\u043d\u0432","\u0424\u0435\u0432","\u041c\u0430\u0440","\u0410\u043f\u0440","\u041c\u0430\u0439","\u0418\u044e\u043d","\u0418\u044e\u043b","\u0410\u0432\u0433","\u0421\u0435\u043d","\u041e\u043a\u0442","\u041d\u043e\u044f","\u0414\u0435\u043a"],"rangeSelectorFrom":"C","rangeSelectorTo":"\u043f\u043e","rangeSelectorZoom":"\u041f\u0435\u0440\u0438\u043e\u0434","thousandsSep":"","resetZoom":"\u0421\u0431\u0440\u043e\u0441\u0438\u0442\u044c \u043c\u0430\u0441\u0448\u0442\u0430\u0431","resetZoomTitle":"\u0423\u0441\u0442\u0430\u043d\u043e\u0432\u0438\u0442\u044c \u043c\u0430\u0441\u0448\u0442\u0430\u0431 1:1","weekdays":["\u0412\u043e\u0441\u043a\u0440\u0435\u0441\u0435\u043d\u044c\u0435","\u041f\u043e\u043d\u0435\u0434\u0435\u043b\u044c\u043d\u0438\u043a","\u0412\u0442\u043e\u0440\u043d\u0438\u043a","\u0421\u0440\u0435\u0434\u0430","\u0427\u0435\u0442\u0432\u0435\u0440\u0433","\u041f\u044f\u0442\u043d\u0438\u0446\u0430","\u0421\u0443\u0431\u0431\u043e\u0442\u0430"]}});

    var chart = $('#test').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,112],[2,114]],"id":"serie 1","name":"serie 1"}, false);
    chart.addSeries({"data":[[2,117],[3,113]],"id":"serie 2","name":"serie 2"}, false);
    chart.redraw();
})();
</script>
EOD;

        $chartTable = Table::create($rows)->setId('test')
            ->withNamedSeries()
            ->withChartDo(function (HighCharts $chart) {
                $chart->langRussian();
            });
        //$chartTable->getHighCharts()->langRussian();
        $this->assertStringEqualsCRLF($e, (string)$chartTable);
    }


    public function testCallbackOption()
    {
        $c = new HighCharts();
        $c->addRow(1, 2);
        $c->setContainerSelector('#test');
        $c->addCallbackOption('xAxis', 'events', 'afterSetExtremes', 'loadPoints');

        $e = <<<'EOD'
<script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#test').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"xAxis":{"events":{"afterSetExtremes":loadPoints}},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,2]],"id":"default","name":"default"}, false);
    chart.redraw();
})();
</script>
EOD;

        $this->assertStringEqualsCRLF($e, (string)$c);
    }


    public function testWithJsonZoom()
    {
        $c = new HighCharts();
        $c->addRow(1, 2);
        $c->withJsonZoom();
        $expected = <<<'EOD'
<div id="highcharts-0"></div>
<script type="text/javascript">
(function(){
        var isReset = false;

    function setExtremesCallback(e) {
        if (e.max == null || e.min == null) {
            isReset = true;
        }
        else {
            isReset = false;
        }
    }

    function loadPoints(e) {

        var url = '?min=:min&max=:max&callback=?',
            chart = $('#hc-container-1').highcharts();

        var min = 1;
        var max = 1;

        if(!isReset)
        {
            min = e.min;
            max = e.max;
        }
        chart.showLoading('Loading data from server...');

        url = url.replace(/:min/g, min).replace(/:max/g, max);

        $.getJSON(url, function (data) {
            var seriesOptions, series;
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    seriesOptions = data[i];
                    if (series = chart.get(seriesOptions.id)) {
                        series.setData(seriesOptions.data, false);
                    }
                    else {
                        chart.addSeries(seriesOptions, false);
                    }
                }
            }
            chart.redraw();
            chart.hideLoading();
        });
    }
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#highcharts-0').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"xAxis":{"events":{"afterSetExtremes":loadPoints,"setExtremes":setExtremesCallback}},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,2]],"id":"default","name":"default"}, false);
    chart.redraw();
})();
</script>
EOD;

        $this->assertStringEqualsSpaceless($expected, (string)$c);

        $this->assertSame(array(
            0 =>
                array(
                    'data' =>
                        array(
                            0 =>
                                array(
                                    0 => 1,
                                    1 => 2,
                                ),
                        ),
                    'id' => 'default',
                    'name' => 'default',
                )
        ),
            $c->getData());
    }


    public function testWithDateAxis()
    {
        $c = new HighCharts();
        $c->addRow(1, 2);
        $c->withDateAxis();
        $c->setContainerSelector('#test');
        $expected = <<<'EOD'
<script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#test').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"xAxis":{"type":"datetime"},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,2]],"id":"default","name":"default"}, false);
    chart.redraw();
})();
</script>
EOD;

        $this->assertStringEqualsCRLF($expected, (string)$c);
    }


    public function testSets()
    {
        $c = new HighCharts();
        $c->setTitle('My Title');
        $c->setType(Series::TYPE_COLUMN);
        $c->setXTitle('My X Title');
        $c->setYTitle('My Y Title');

        $expected = <<<'EOD'
<div id="highcharts-1"></div>
        <script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#highcharts-1').highcharts({"title":{"text":"My Title"},"chart":{"zoomType":"x","type":"column"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"xAxis":{"title":{"text":"My X Title"}},"yAxis":{"title":{"text":"My Y Title"}},"series":[]}).highcharts();
    chart.redraw();
})();
</script>
EOD;
        $this->assertStringEqualsSpaceless($expected, (string)$c);


    }


    public function testDefaultSeries()
    {
        $c = new HighCharts();
        $s = new Series();
        $s->setType(Series::TYPE_AREA_SPLINE);
        $c->setDefaultSeries($s);
        $c->addRow(1, 2);

        $expected = <<<'EOD'
<div id="highcharts-2"></div>
        <script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#highcharts-2').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"series":[]}).highcharts();
    chart.addSeries({"data":[[1,2]],"id":"default","name":"default","type":"areaspline"}, false);
    chart.redraw();
})();
</script>
EOD;
        $this->assertStringEqualsSpaceless($expected, (string)$c);
    }


    public function testOptions()
    {
        $c = new HighCharts();
        $c->addOptions(array('xAxis' => array('title' => 'tttt')));
        $c->addOption('xAxis', 'test1', 'test2', 'test-value');

        $this->assertSame(true, $c->isEmpty());

        $expected = <<<'EOD'
<div id="highcharts-3"></div>
        <script type="text/javascript">
(function(){
    Highcharts.setOptions({"global":{"useUTC":false}});

    var chart = $('#highcharts-3').highcharts({"title":false,"chart":{"zoomType":"x"},"legend":{"enabled":true,"verticalAlign":"top"},"plotOptions":{"series":{"marker":{"enabled":false}}},"tooltip":{"crosshairs":[true,true],"shared":false},"credits":{"enabled":false},"xAxis":{"title":"tttt","test1":{"test2":"test-value"}},"series":[]}).highcharts();
    chart.redraw();
})();
</script>
EOD;
        $this->assertStringEqualsSpaceless($expected, (string)$c);
    }

}