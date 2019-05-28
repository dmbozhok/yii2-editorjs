<?php
namespace x51\yii2\modules\editorjs;

use \Yii;
use \yii\helpers\Html;
use \yii\helpers\Json;
use \yii\helpers\Url;

class Module extends \yii\base\Module
{
    public $uploadDir = 'upload';
    protected $absUploadDir = false;
    public $useDateDir = true;
    public $useUserIdDir = true;
    public $maxImageWidth = 1000;
    public $uploadImageFromUrl = true;
    public $classRender = '\\x51\\yii2\\modules\\editorjs\\classes\\Render';
    public $jsClass = 'Ejs';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = '\x51\yii2\modules\editorjs\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultController = 'default';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->uploadDir = trim($this->uploadDir);
        if (strpos($this->uploadDir, '/') === 0) {
            // абсолютный путь
            $this->absUploadDir = true;
        } else {
            // относительный путь
            $this->absUploadDir = false;
        }
        $view = Yii::$app->view;
        Yii::$app->view->registerAssetBundle('\x51\yii2\modules\editorjs\assets\Assets');


        /*if (file_exists($this->uploadDir) && is_dir($this->uploadDir)) {
        $this->absUploadDir = true;
        } else {
        $this->absUploadDir = false;
        }*/
        //var_dump($this->absUploadDir);var_dump($this->uploadDir);var_dump(file_exists($this->uploadDir));var_dump(realpath($this->uploadDir));die;
        parent::init();
    } // end init

    public function getAbsUploadDir()
    {
        return $this->absUploadDir;
    }

    public function editorjs($id, $form = false, $name = false, $jsonContent = '', $options = [])
    {
        $view = Yii::$app->view;
        $request = Yii::$app->request;
        if ($name && $request->isPost) {
            if ($reqContent = $request->post($name)) {
                $jsonContent = $reqContent;
            }
        }

        if ($form) {
            if (!$name) {
                $name = $id;
            }
            echo Html::hiddenInput($name, $jsonContent, [
                'form' => $form,
            ]);
        }
        $options_str = '';
        if ($options) {
            if (is_array($options)) {
                foreach ($options as $key => $val) {
                    $options_str .= ' ' . $key . '="' . $val . '"';
                }
            } elseif (is_string($options)) {
                $options_str = $options;
            }
        }
        echo '<div id="' . $id . '"' . $options_str . '></div>';

        $js='(function(){';
        $js .= 'var e = new '.$this->jsClass.'("' . $id . '"';
        $js .= ', "' . Url::to(['/' . $this->id . '/upload/file']) . '"';
        $js .= ', "' . Url::to(['/' . $this->id . '/upload/url']) . '"';
        $js .= ', "' . Url::to(['/' . $this->id . '/upload/link']) . '"';
        if ($form) {
            $js .= ', "' . $form . '"';
            $js .= ', "' . $name . '"';
        } else {
            $js .= ', null, null';
        }
        if ($jsonContent) {
            $js .= ', ' . Json::htmlEncode($jsonContent);
        }
        $js .= ');';
        $js.='})();';

        
        $view->registerJs($js);
    } // end editorjs

    public function renderFromJsonToHtml($jsonContent)
    {
        $cn = $this->classRender;
        return (new $cn())->renderFromJsonToHtml($jsonContent);
    } // end renderFromJson

} // end class
