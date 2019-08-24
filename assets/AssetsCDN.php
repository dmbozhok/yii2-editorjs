<?php
namespace x51\yii2\modules\editorjs\assets;

class AssetsCDN extends \yii\web\AssetBundle
{
    protected $arCdnComponents = [
		'@editorjs/editorjs',
		'@editorjs/table',
		'@editorjs/header',
		'@editorjs/list',
		'@editorjs/paragraph',
		'@editorjs/warning',
		'@editorjs/code',
		'@editorjs/delimiter',
		'@editorjs/inline-code',
		'@editorjs/quote',
		'@editorjs/marker',
		'@editorjs/raw',
		'@editorjs/embed',
		'@editorjs/checklist',
		'@quanzo/metaparam',
		'@quanzo/change-font-size',
		'@quanzo/personality',
		'@editorjs/image',
		'@editorjs/link'
	];
	
	
	
	public $sourcePath = __DIR__.'/dist';
    /*public $css = [
        
    ];*/
    public $js = [];
    public $depends = [];
    public function init() {
        /*$arList = scandir($this->sourcePath.'//js//');
        foreach($arList as $fn) {
            if (strpos($fn, 'editor.')===0 && strrpos($fn, '.js')!==false) {
                $this->js[] = 'js/'.$fn;
            }
        }
        $this->js[] = 'js/init.js';*/
		foreach ($this->arCdnComponents as $packet) {
			$this->js[] = 'https://cdn.jsdelivr.net/npm/'.$packet.'@latest';
		}
		$this->js[] = 'init.bundle.js';
		parent::init();
    }

} // end class
