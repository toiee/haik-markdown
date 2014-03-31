<?php
namespace Toiee\HaikMarkdown\Plugin\Bootstrap\Cols;

use Toiee\HaikMarkdown\HaikMarkdown;
use Toiee\HaikMarkdown\Plugin\Bootstrap\Plugin;
use Toiee\HaikMarkdown\Plugin\Bootstrap\Row;
use Toiee\HaikMarkdown\Plugin\Bootstrap\Column;
use Michelf\MarkdownInterface;

class ColsPlugin extends Plugin {

    const COL_DELIMITER   = "\n====\n";

    public static $PREFIX_CLASS_ATTRIBUTE = 'haik-plugin-cols';

    protected $delimiter;

    protected $row;
    protected $colBase;
    
    protected $params;
    protected $body;
    
    protected $violateColumnSize;

    protected $view = 'cols.template';
    
    public function __construct(MarkdownInterface $parser)
    {
        parent::__construct($parser);

        $class_name = get_called_class();
        $this->row = $this->createRow()->prependClassAttribute($class_name::$PREFIX_CLASS_ATTRIBUTE);
        $this->delimiter = self::COL_DELIMITER;
        $this->violateColumnSize = false;
    }

    /**
     * Create Row instance
     *
     * @return Row
     */
    protected function createRow()
    {
        return new Row();
    }

    /**
     * Create Column instance
     *
     * @param 
     * @return Toiee\HaikMarkdown\GridSystem\ColumnInterface
     */
    protected function createColumn($text = '')
    {
        return new Column($text);
    }

    /**
     * convert call via HaikMarkdown :::{plugin-name(...):::
     * @params array $params
     * @params string $body when {...} was set
     * @return string converted HTML string
     * @throws RuntimeException when unimplement
     */
    public function convert($params = array(), $body = '')
    {

        // set params
        $this->params = $params;
        $this->body = $body;
        
        $this->parseParams();
        $this->parseBody();
        
        $this->validatesColumnSize();

        $this->parseColumns();

        $html = $this->renderView();

        return $html;
    }

    protected function validatesColumnSize()
    {
        $row_class_name = get_class($this->row);
        if ($this->getTotalColumnSize() > $row_class_name::$COLUMN_SIZE)
        {
            $this->violateColumnSize = true;
        }
    }

    protected function getTotalColumnSize()
    {
        $total_columns = 0;
        foreach ($this->row as $column)
        {
            $total_columns += $column->getColumnWidth() + $column->getOffsetWidth();
        }
        return $total_columns;
    }

    /**
     * parse params
     */
    protected function parseParams()
    {
        foreach ($this->params as $param)
        {
            if ($this->columnIsParsable($param))
            {
                $this->addColumns($param);
            }
            else
            {
                if (preg_match('/^class=(.+)$/', $param, $mts))
                {
                    // if you want add class to top div
                    $this->row->addClassAttribute(trim($mts[1]));
                }
                else
                {
                    // if you want change delimiter
                    $this->delimiter = "\n" . trim($param) . "\n";
                }
            }
        }
    }

    protected function columnIsParsable($text)
    {
        return Column::isParsable($text);
    }

    /**
     * Add columns by text
     *
     * @param string $text Column::isParsable is true
     * @return void
     */
    protected function addColumns($text)
    {
        $column = $this->createColumn($text);
        $this->row[] = $column;
    }

    /**
     * Set columns by body
     *
     * @return void
     */
    protected function setColumnsByBody()
    {
        if (count($this->row) === 0)
        {
            // if parameter is not set then make cols with body
        	$data = explode($this->delimiter, $this->body);
        	$row_class_name = get_class($this->row);
    		$col_width = (int)($row_class_name::$COLUMN_SIZE / count($data));
    		for ($i = 0; $i < count($data); $i++)
    		{
    		    $column = $this->createColumn()->setColumnWidth($col_width);
                $this->row[$i] = $column;
    		}
        }
    }
    /**
     * parse body
     */
    protected function parseBody()
    {
        $this->setColumnsByBody();

        // if parameter and body delimiter is not match then bind body over cols
        $col_num = count($this->row);
        $data = array_pad(explode($this->delimiter, $this->body, $col_num), $col_num, '');

    	for ($i = 0; $i < $col_num; $i++)
    	{
    	    $column = $this->row[$i];
    		if (isset($data[$i]))
    		{
    		    if (preg_match('/(?:^|\n)STYLE:(.+?)\n/', $data[$i], $mts))
    		    {
    		        $this->row[$i]->addStyleAttribute($mts[1]);
        		    $data[$i] = preg_replace('/'.preg_quote($mts[0], '/'). '/', '', $data[$i], 1);
    		    }

    		    if (preg_match('/(?:^|\n)CLASS:(.+?)\n/', $data[$i], $mts))
    		    {
    		        $this->row[$i]->addClassAttribute($mts[1]);
        		    $data[$i] = preg_replace('/'.preg_quote($mts[0], '/'). '/', '', $data[$i], 1);
    		    }

    		    $this->row[$i]->setContent($data[$i]);
    		}
    	}
    }

    /**
     * Parse columns content's markdown
     */
    protected function parseColumns()
    {
        foreach ($this->row as $i => $column)
        {
            $this->row[$i]->setContent(trim($this->parser->transform($column->getContent())));
        }
    }

    public function renderView($data = array())
    {
        return $this->row->render();
    }

}
