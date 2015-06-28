<?php

namespace Yaoi\View\HighCharts;

use Closure;
use Yaoi\View\Table\Renderer;
use Yaoi\Rows\Processor;
use Yaoi\View\HighCharts;

class Table extends Renderer
{
    const DATA_TYPE_REGULAR = 'regular';
    const DATA_TYPE_NAMED = 'named';

    public $dataType = self::DATA_TYPE_REGULAR;
    protected $tag = 'div';
    protected $content = '';

    /**
     * @var HighCharts
     */
    private $highCharts;

    public function __construct(&$rows = null)
    {
        parent::__construct($rows);
        $this->highCharts = new HighCharts();
    }

    static $uniqueId = 0;

    protected function renderHead()
    {
        if (null === $this->id) {
            $this->id = 'hc-container-' . ++self::$uniqueId;
        }
        $this->highCharts->setContainerSelector('#' . $this->id);
        parent::renderHead();
    }

    /**
     * @return $this
     * @deprecated see withNamedSeries
     */
    public function withRegularSeries()
    {
        $this->dataType = self::DATA_TYPE_REGULAR;
        return $this;
    }

    /**
     * @return $this
     * @deprecated use Rows_Processor::create($rows)->combineOffset(2, 1) instead of rows
     */
    public function withNamedSeries()
    {
        $this->dataType = self::DATA_TYPE_NAMED;
        return $this;
    }

    private function seriesFill()
    {
        if (self::DATA_TYPE_NAMED === $this->dataType) {
            $this->rows = Processor::create($this->rows)->combineOffset(2, 1);
        }

        foreach ($this->rows as $row) {

            $xValue = array_shift($row);
            foreach ($row as $key => $value) {
                $this->highCharts->addRow($xValue, $value, $key);
            }
        }
    }

    public function withChartDo(Closure $closure)
    {
        $closure($this->highCharts);
        return $this;
    }


    /**
     * @return HighCharts
     */
    public function getHighCharts()
    {
        return $this->highCharts;
    }

    protected function renderTail()
    {
        parent::renderTail();

        $this->seriesFill();

        $this->highCharts->render();
    }


    public function getData()
    {
        $this->seriesFill();
        return $this->highCharts->getData();
    }

    public function renderJson()
    {
        $this->seriesFill();
        $this->highCharts->renderJson();
    }

}