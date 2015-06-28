<?php

namespace Yaoi\View;

use Yaoi\BaseClass;

class HighCharts extends BaseClass implements Renderer
{

    public function isEmpty()
    {
        return empty($this->series);
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    private $options;
    /**
     * @var HighCharts\Series[]
     */
    private $series = array();


    public function __construct()
    {
        $this->options = array(
            'title' => false,

            /*
            'chart' => array(
                'resetZoomButton' => array(
                    'position' => array(
                        'align' => 'left', // by default
                        'verticalAlign' => 'bottom', // by default
                        'x' => 0,
                        'y' => -130,
                    )
                )
            ),
            */

            'chart' => array(
                'zoomType' => 'x'
            ),

            'legend' => array(
                'enabled' => true,
                //'layout' => 'vertical',
                'verticalAlign' => 'top'
            ),

            'plotOptions' => array(
                'series' => array(
                    'marker' => array(
                        'enabled' => false
                    )
                )
            ),

            'tooltip' => array(
                'crosshairs' => array(true, true),
                'shared' => false,
            ),

            'credits' => array(
                'enabled' => false
            )

        );
    }


    public function setType($type = HighCharts\Series::TYPE_LINE)
    {
        $this->options['chart']['type'] = $type;
        return $this;
    }


    public function setTitle($title)
    {
        if ($title) {
            $this->options['title']['text'] = $title;
        } else {
            $this->options['title'] = false;
        }
        return $this;
    }

    public function setYTitle($title)
    {
        if ($title) {
            $this->options['yAxis']['title']['text'] = $title;
        } else {
            $this->options['yAxis']['title']['text'] = null;
        }
        return $this;
    }

    public function setXTitle($title)
    {
        if ($title) {
            $this->options['xAxis']['title']['text'] = $title;
        } else {
            $this->options['xAxis']['title']['text'] = null;
        }
        return $this;
    }


    private $literalDateAxis;

    public function withDateAxis($literal = false)
    {
        $this->options['xAxis']['type'] = 'datetime';
        $this->literalDateAxis = $literal;
        return $this;
    }


    public function addOptions($options)
    {
        $this->options = \Yaoi\Misc::arrayMergeRecursiveDistinct($this->options, $options);
        return $this;
    }


    private $unquote = false;

    public function addCallbackOption()
    {
        $this->unquote = true;
        $args = func_get_args();
        $value = 'unquoted' . array_pop($args) . 'unquoted';
        $t = &$this->options;
        foreach ($args as $arg) {
            $t = &$t[$arg];
        }
        $t = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function addOption()
    {
        $args = func_get_args();
        $value = array_pop($args);
        $t = &$this->options;
        foreach ($args as $arg) {
            $t = &$t[$arg];
        }
        $t = $value;
        return $this;
    }

    /**
     * @var HighCharts\Series
     */
    private $defaultSeries;

    /**
     * @param mixed $defaultSeries
     * @return $this
     */
    public function setDefaultSeries(HighCharts\Series $defaultSeries = null)
    {
        $this->defaultSeries = $defaultSeries;
        return $this;
    }


    public function addRow($x, $y, $id = 'default')
    {
        if ($this->literalDateAxis) {
            $x = 1000 * strtotime($x);
        }

        //echo 'row added';
        if (!$series = &$this->series[$id]) {
            if ($this->defaultSeries) {
                $series = clone $this->defaultSeries;
            } else {
                $series = new HighCharts\Series();
            }
            $series->setId($id);
        }
        if (isset($this->ranges[$id])) {
            $series->addRangeRow($x, $y, $this->ranges[$id]);
        } else {
            $series->addRow($x, $y);
        }
        return $this;
    }

    private $ranges = array();

    public function addSeries(HighCharts\Series $series, $rangeHighId = null)
    {
        $this->series[$rangeLowId = $series->getId()] = $series;
        if (null !== $rangeHighId) {
            $this->series[$rangeHighId] = $series;
            $this->ranges[$rangeLowId] = HighCharts\Series::VALUE_LOW;
            $this->ranges[$rangeHighId] = HighCharts\Series::VALUE_HIGH;
        }
        return $this;
    }


    private $containerSelector;

    public function setContainerSelector($id)
    {
        $this->containerSelector = $id;
        return $this;
    }


    public $globalOptions = array(
        'global' => array(
            'useUTC' => false
        ),
    );

    public function langRussian()
    {
        $this->globalOptions['lang'] = array(
            'shortMonths' => array('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'),
            'rangeSelectorFrom' => 'C',
            'rangeSelectorTo' => 'по',
            'rangeSelectorZoom' => 'Период',
            'thousandsSep' => '',
            'resetZoom' => 'Сбросить масштаб',
            'resetZoomTitle' => 'Установить масштаб 1:1',
            'weekdays' => array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота')
        );
        $this->jsonLoadingText = 'Загрузка...';

        return $this;
    }


    private $jsonLoadingText = 'Loading data from server...';
    private $withJsonZoom;
    private $jsonUrl;

    public function withJsonZoom($jsonUrl = null)
    {
        $this->withJsonZoom = true;
        $this->jsonUrl = $jsonUrl;
        return $this;
    }


    private $minX;
    private $maxX;

    private function countExtremes()
    {
        $this->minX = 0;
        $this->maxX = 0;
        foreach ($this->series as $series) {
            $this->minX = $series->minX;
            $this->maxX = $series->maxX;
            break;
        }
        foreach ($this->series as $series) {
            $this->minX = min($this->minX, $series->minX);
            $this->maxX = max($this->maxX, $series->maxX);
        }
    }

    private static $autoContainerId = 0;
    private static $jsLoaded = false;

    public function render()
    {
        if ($this->withJsonZoom) {
            $this->addCallbackOption('xAxis', 'events', 'afterSetExtremes', 'loadPoints');
            $this->addCallbackOption('xAxis', 'events', 'setExtremes', 'setExtremesCallback');
            if (null === $this->jsonUrl) {
                $this->jsonUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                if (strpos($this->jsonUrl, '?') !== false) {
                    $this->jsonUrl .= '&';
                } else {
                    $this->jsonUrl .= '?';
                }
                $this->jsonUrl .= 'min=:min&max=:max&callback=?';
            }
            $this->countExtremes();

        }

        $this->options['series'] = array();
        //var_dump($this->series);
        $options = json_encode($this->options);
        if ($this->unquote) {
            $options = str_replace(array('"unquoted', 'unquoted"'), '', $options);
        }

        if (!$this->containerSelector) {
            $this->containerSelector = 'highcharts-' . self::$autoContainerId++;
            ?>
<div id="<?php echo $this->containerSelector ?>"></div>
<?php
            $this->containerSelector = '#' . $this->containerSelector;
        }

        if (!self::$jsLoaded) {
            ?>
<script type="text/javascript" src="http://code.highcharts.com/stock/highstock.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts-more.js"></script>
<?php
            self::$jsLoaded = true;
        }

        ?>
<script type="text/javascript">
(function(){
    <?php
    if ($this->withJsonZoom) {
    ?>
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

        var url = '<?php echo $this->jsonUrl?>',
            chart = $('#hc-container-1').highcharts();

        var min = <?php echo $this->minX ?>;
        var max = <?php echo $this->maxX ?>;

        if(!isReset)
        {
            min = e.min;
            max = e.max;
        }
        chart.showLoading('<?php echo $this->jsonLoadingText?>');

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
<?php
    }
    ?>Highcharts.setOptions(<?php echo json_encode($this->globalOptions)?>);

    var chart = $('<?php echo $this->containerSelector ?>').highcharts(<?php echo $options ?>).highcharts();
    <?php
    if ($this->withJsonZoom && empty($this->series)) {
        ?>isReset = true;
    loadPoints();
    <?php
        } else {
            foreach ($this->series as $id => $series) {
                if (isset($this->ranges[$id]) && HighCharts\Series::VALUE_HIGH == $this->ranges[$id]) {
                    continue;
                }
            ?>chart.addSeries(<?php echo json_encode($series->exportOptions())?>, false);
    <?php
        }
    }

?>chart.redraw();
})();
</script><?php
    }


    /**
     * @return array
     */
    public function getData()
    {
        $data = array();
        foreach ($this->series as $series) {
            $data [] = $series->exportOptions();
        }
        return $data;
    }


    /**
     * @return $this
     */
    public function renderJson()
    {
        Jsonp::create($this->getData(), $_GET['callback'])->render();
        return $this;
    }


}