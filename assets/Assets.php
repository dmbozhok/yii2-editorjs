<?php
namespace x51\yii2\modules\editorjs\assets;

class Assets extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__.'/dist';
    /*public $css = [
        
    ];*/
    public $js = [        
		'ejs.bundle.js',		
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
    public function init() {
        /*$arList = scandir($this->sourcePath.'//js//');
        foreach($arList as $fn) {
            if (strpos($fn, 'editor.')===0 && strrpos($fn, '.js')!==false) {
                $this->js[] = 'js/'.$fn;
            }
        }
        $this->js[] = 'js/init.js';*/
		parent::init();
    }

} // end class
