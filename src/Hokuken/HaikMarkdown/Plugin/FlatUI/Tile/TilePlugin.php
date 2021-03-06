<?php
namespace Hokuken\HaikMarkdown\Plugin\FlatUI\Tile;

use Hokuken\HaikMarkdown\Plugin\FlatUI\Plugin;
/* use Hokuken\HaikMarkdown\Plugin\Bootstrap\Thumbnails\ThumbnailsPlugin as BootstrapThumbnailsPlugin; */
use Hokuken\HaikMarkdown\Plugin\Bootstrap\Cols\ColsPlugin as BootstrapColsPlugin;


class TilePlugin extends BootstrapColsPlugin {

    public static $PREFIX_CLASS_ATTRIBUTE = 'haik-plugin-tile';

    /**
     * Create Column instance
     *
     * @param 
     * @return Hokuken\HaikMarkdown\GridSystem\ColumnInterface
     */
    protected function createColumn($text = '')
    {
        $hot = false;
        $text = preg_replace_callback('/\.(hot|popular|tile-hot)\b/', function($matches) use (&$hot)
        {
            $hot = ' tile-hot';
            return '';
        },
        $text);

        $column =  parent::createColumn($text);
        $column->hot = $hot;
        
        return $column;
    }
    
    /**
     * Parse columns content's markdown
     *
     * @see Hokuken\HaikMarkdown\Plugin\Bootstrap\Cols\ColsPlugin::parseColumns
     */
    protected function parseColumns()
    {

        foreach ($this->row as $i => $column)
        {
            $lines = preg_split('{ \n+ }mx', trim($column->getContent()));

            $top_line = $this->parser->transform($lines[0]);
            if (strpos($top_line, '<img') !== FALSE)
            {
                $image = trim(strip_tags($top_line, '<a><img>'));
                $image = str_replace(
                           '<img', '<img class="tile-image big-illustration"',
                           strip_tags($image, '<img>')
                         );

                $column->thumbnail = $image;
                array_shift($lines);
            }

            $body = join("\n", $lines);
            $body = $this->parser->transform($body);
            if ( ! preg_match('{ <h[1-6][^>]*?class=".*?" }mx', $body))
            {
                $body = preg_replace('{ <h([1-6])(.*?>) }mx', '<h\1 class="tile-title"\2', $body);
            }
            $column->setContent($body);
            
            $this->row[$i] = $column;
        }
    }

    /**
     * Render view
     *
     * @see Hokuken\HaikMarkdown\Plugin\Bootstrap\Cols\ColsPlugin::renderView
     */
    public function renderView($data = array())
    {
        foreach ($this->row as $column)
        {
            $thumbnail = isset($column->thumbnail) ? $column->thumbnail : '';
            $hot = ($column->hot === false) ? '': $column->hot;
            
            $content = $column->getContent();
            
            $content = '<div class="tile'.$hot.'">'.$thumbnail.$content.'</div>';
            $column->setContent($content);
        }
        return parent::renderView($data);
    }

}
