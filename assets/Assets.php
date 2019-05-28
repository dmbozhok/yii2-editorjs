<?php
namespace x51\yii2\modules\editorjs\assets;

class Assets extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__;
    /*public $css = [
        
    ];*/
    public $js = [
        /*'js/editor.js',
		'js/editor.header.js',
		'js/editor.list.js',
		'js/editor.paragraph.js',
		'js/editor.warning.js',
        'js/editor.code.js',
        'js/editor.simpleimage.js',
        'js/editor.image.js',
        'js/editor.delimiter.js',
        'js/editor.table.js',
		'js/editor.inline.js',
		'js/editor.quote.js',
		'js/editor.raw.js',
		'js/editor.embed.js',
        'js/editor.markertool.js',
        'js/editor.link.js',
        'js/init.js',*/
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
    public function init() {
        $arList = scandir($this->sourcePath.'//js//');
        foreach($arList as $fn) {
            if (strpos($fn, 'editor.')===0 && strrpos($fn, '.js')!==false) {
                $this->js[] = 'js/'.$fn;
            }
        }
        $this->js[] = 'js/init.js';
    }

} // end class
