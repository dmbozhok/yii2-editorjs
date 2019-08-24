<?php
namespace x51\yii2\modules\editorjs\classes;

use \yii\helpers\Json;
use \Yii;

class Render
{
    public function renderFromJsonToHtml($jsonContent)
    {
        $result = '';
        if ($jsonContent) {
            $arContent = Json::decode($jsonContent, true);
            if (!empty($arContent['blocks'])) {
                foreach ($arContent['blocks'] as $block) {
                    $m = 'block_' . $block['type'];
                    if (method_exists($this, $m)) {
                        $result .= $this->$m($block['data']);
                    }
                }
            }
        }
        return $result;
    } // end renderFromJsonToHtml

    public function renderBlock(array $block)
    {
        if (isset($block['type'])) {
            $m = 'block_' . $block['type'];
        } else {
            $m = 'block_';
        }
        if (isset($block['data']) && is_array($block['data'])) {
            if (method_exists($this, $m)) {
                return $this->$m($block['data']);
            }
        }
        return '';
    }

    protected function block_(array $data)
    {
        return '';
    }

    public function block_header(array $data)
    {
        $level = 2;
        if (!empty($data['level'])) {
            $level = intval($data['level']);
        }
        return '<h' . $level . '>' . $data['text'] . '</h' . $level . '>';
    }

    
    public function block_list(array $data)
    {
        $listStyle = $data['style'] == 'ordered' ? 'ol' : 'ul';
        $result = '<' . $listStyle . '>';
        foreach ($data['items'] as $item) {
            $result .= '<li>' . $item . '</li>';
        }
        return $result . '</' . $listStyle . '>';
    }
    public function block_code(array $data)
    {
        return '<code>' . $data['code'] . '</code>';
    }
    public function block_paragraph(array $data)
    {
        return '<p>' . $data['text'] . '</p>';
    }
    public function block_text(array $data)
    {
        return '<p>' . $data['text'] . '</p>';
    }
    public function block_image(array $data)
    {
        $class = 'image';
        if (!empty($data['stretched']) && empty($data['withBackground'])) {
            $class .= ' stretched';
        }
        if (!empty($data['withBackground'])) {
            $class .= ' with-background';
        }
        if (!empty($data['withBorder'])) {
            $class .= ' with-border';
        }
        if ($class) {
            $class = ' class="' . $class . '"';
        }
        if (!empty($data['caption'])) {
            $caption = htmlentities($data['caption']);
            $class .= ' alt="' . $caption . '" title="' . $caption . '"';
        }
        if (!empty($data['file']['url'])) {
            return '<img src="' . $data['file']['url'] . '"' . $class . '>';
        }
        return '';
    }
    public function block_embed(array $data)
    {
        $result = '';
        //$result .= print_r($block, true);
        switch ($data['service']) {
            case 'youtube':{
                    $result .= '<div class="embed ' . $data['service'] . '">
                                    <iframe width="' . $data['width'] . '" height="' . $data['height'] . '" src="' . $data['embed'] . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    if ($data['caption']) {
                        $result .= '<div class="caption">' . $data['caption'] . '</div>';
                    }
                    $result .= '</div>';
                    break;
                }
            /*Array ( [type] => embed [data] => Array ( [service] => coub [source] => https://coub.com/view/1gjx9p [embed] => https://coub.com/embed/1gjx9p [width] => 580 [height] => 320 [caption] => ) ) */
            case 'coub':{
                    $result = '<div class="embed ' . $data['service'] . '">
                                    <iframe src="' . $data['embed'] . '" allowfullscreen frameborder="0" width="' . $data['width'] . '" height="' . $data['height'] . '" allow="autoplay"></iframe>';
                    if ($data['caption']) {
                        $result .= '<div class="caption">' . $data['caption'] . '</div>';
                    }
                    $result .= '</div>';
                    break;
                }
        }
        return $result;
    }
    public function block_delimiter(array $data)
    {
        return '<div class="delimiter"></div>';
    }
    public function block_table(array $data)
    {
        $result = '<table>';
        foreach ($data['content'] as $row) {
            $result .= '<tr>';
            foreach ($row as $e) {
                $result .= '<td>' . $e . '</td>';
            }
            $result .= '</tr>';
        }
        return $result . '</table>';
    }
    public function block_quote(array $data)
    {
        $class = ' class="align-' . $data['alignment'] . '"';
        $result = '<blockquote' . $class . '>' . $data['text'];
        if ($data['caption']) {
            $result .= '<cite>' . $data['caption'] . '</cite>';
        }
        return $result . '</blockquote>';
    }
    public function block_raw(array $data)
    {
        $result = $data['html'];
		return $result;
    }
	public function block_warning(array $data) {
		$result = '<div class="alert alert-warning">';
			if ($data['title']) {
				$result.='<div class="title">'.$data['title'].'</div>';
			}
			$result.='<div class="message">'.$data['message'].'</div>';
		$result.='</div>';
		return $result;
	}
	public function block_checklist(array $data) {
		$result = '<ul class="checklist">';
		foreach ($data['items'] as $item) {
			$result.='<li class="'.($item['checked'] ? 'checked' : 'unchecked').'">'.$item['text'].'</li>';
		}
		return $result.'</ul>';
    }
    public function block_personality(array $data) {
        $result = '<div class="personality">';
        $name = '';
        $nameHtml = '';
        if (!empty($data['name'])) {
            $name = $data['name'];
            $nameHtml = htmlentities($data['name'], ENT_HTML5 | ENT_QUOTES);
        }        
        $result .= '<div class="info">';
        if ($name) {
            $result.='<div class="person_name">' . $name . '</div>';
        }

        if (!empty($data['description'])) {
            $result.='<div class="person_desc">' . $data['description'] . '</div>';
        }
        if (!empty($data['link'])) {
            //$_SERVER['REQUEST_SCHEME']
            $result.='<div class="person_link"><a href="'.htmlentities($data['link'], ENT_HTML5 | ENT_QUOTES).'" title="'.$nameHtml.'">'.$data['link'].'</a></div>';
        }
        $result.='</div>';
        if (!empty($data['photo'])) {
            $result .= '<img src="' . $data['photo'] . '" title="' . $nameHtml . '">';
        }

        return $result . '</div>';
    }
    
    public function block_metaparam(array $data) {
        $view = Yii::$app->view;
        if (!empty($data['title'])) {
            $view->title = $data['title'];
        }
        if (!empty($data['description'])) {
            $view->registerMetaTag([
                'name' => 'description',
                'content' => addslashes(strip_tags($data['description']))
            ]);            
        }
        if (!empty($data['keywords'])) {
            $view->registerMetaTag([
                'name' => 'keywords',
                'content' => addslashes(strip_tags($data['keywords'])),
            ]);
        }
    }
} // class
