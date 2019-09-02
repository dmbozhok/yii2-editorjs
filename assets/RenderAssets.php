<?php
namespace x51\yii2\modules\editorjs\assets;

class RenderAssets extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__;
    public $css = [
        'dist/render.css'
    ];
    public $js = [];
    public $depends = [
    ];
    public function init() {
        parent::init();
    }

} // end class
