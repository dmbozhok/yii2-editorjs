<?php
namespace x51\yii2\modules\editorjs;

use \Yii;
use \yii\helpers\Html;
use \yii\helpers\Json;
use \yii\helpers\Url;
use \x51\yii2\modules\editorjs\models\StaticPage;

class Module extends \yii\base\Module
{
    public $name = '';
    public $description = '';

    public $uploadDir = 'upload'; // имя каталога в корне сайта для сохранения информации
    protected $absUploadDir = false; // признак того, что $uploadDir задан как абсолютный
    public $useDateDir = true; // картинки будут загрузаться по папкам с текущей датой
    public $useUserIdDir = true; // картинки будут разложены по папкам с id пользователей
    public $maxImageWidth = 1000; // ограничение на размер картинки
    public $uploadImageFromUrl = true; // разрешение на загрузку картинки по url (см контроллер загрузки)
    public $classRender = '\\x51\\yii2\\modules\\editorjs\\classes\\Render'; // класс для преобразования json (editorjs) в html
    public $jsClass = 'Ejs'; // имя класса в ресурсах (папка assets), который является оберткой editorjs для старта
	public $useCDN = false; // использовать загрузку javascript через cdn.jsdelivr.net 

    public $staticPageDir = '@app/pages/'; // путь к папке со статичными страницами editorjs
    public $extWithDot = '.json';
    public $shortcode = ''; // имя модуля обработки шорткодов (если нужна обработка)
	
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
        if (!isset($this->module->i18n->translations['module/editorjs'])) {
            $this->module->i18n->translations['module/editorjs'] = [
                'class' => '\yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'module/editorjs' => 'messages.php',

                ],
            ];
            $this->module->i18n->translations['module/editorjs/static'] = [
                'class' => '\yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [

                    'module/editorjs/static' => 'static.php',
                ],
            ];

        }

        $this->uploadDir = trim($this->uploadDir);
        if (strpos($this->uploadDir, '/') === 0) {
            // абсолютный путь
            $this->absUploadDir = true;
        } else {
            // относительный путь
            $this->absUploadDir = false;
        }
        

        parent::init();
    } // end init

    public function getAbsUploadDir()
    {
        return $this->absUploadDir;
    }

    /**
     * Создает необходимые поля и вызывает editor.js для редактирования контента
     *
     * @param string $id задает id для блока html, а такде имя если не указан параметр name
     * @param boolean $form задает имя формы
     * @param boolean $name задает имя параметра
     * @param string $jsonContent сохраненный контент для дальнейшего редактирования
     * @param array $options опции для html контейнера
     * @return void
     */
    public function editorjs($id, $form = false, $name = false, $jsonContent = '', $placeholder = '', $options = [])
    {
        $view = Yii::$app->view;
		if ($this->useCDN) {
			$view->registerAssetBundle('\x51\yii2\modules\editorjs\assets\AssetsCDN');
		} else {
			$view->registerAssetBundle('\x51\yii2\modules\editorjs\assets\Assets');
		}

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

        $js = '(function(){';
        $js .= 'var e = new ' . $this->jsClass . '("' . $id . '"';
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
        if ($placeholder) {
            $js .= ', "' . $placeholder . '"';
        }
        $js .= ');';
        $js .= '})();';

        $view->registerJs($js);
    } // end editorjs

    public function renderFromJsonToHtml($jsonContent)
    {
        $cn = $this->classRender;
        $content = (new $cn())->renderFromJsonToHtml($jsonContent);
        if ($this->shortcode) {
            if ($shortcodeMod = Yii::$app->getModule($this->shortcode)) {
                return $shortcodeMod->process($content);
            }
        }
        return $content;
    } // end renderFromJson

    /**
     * Возвращает html содержимое статичной страницы
     *
     * @param string $name
     * @return null|string
     */
    public function renderStaticPage($name) {
        if ($this->staticPageDir) {
            $model = new StaticPage($this->staticPageDir, $this->extWithDot, $name);
            if ($model->exists()) {
                return $this->renderFromJsonToHtml($model['content']);
            }
        }
        return null;
    }

    

    /**
     * Возвращает содержимое статичной страницы в виде массива блоков
     *
     * @param string $name
     * @return array|null
     */
    public function getStaticPageBlocksArray($name) {
        if ($this->staticPageDir) {
            $model = new StaticPage($this->staticPageDir, $this->extWithDot, $name);
            if ($model->exists()) {
                if ($ar = json_decode($model['content'], true)) {
                    if (!empty($ar['blocks'])) {
                        return $ar['blocks'];
                    } else {
                        return [];
                    }
                }
            }
        }
        return null;

    } // end getStaticPageContentArray

    // ISupportAdminPanel
    public function apModuleName() {
        if ($this->name) {
            return $this->name;
        } else {
            return Yii::t('module/editorjs', 'Editorjs module');
        }        
    }

    public function apModuleDesc() {
        return $this->description;
    }

    public function apAdminMenu() {
        return [
            ['label' => Yii::t('module/editorjs', 'Static pages'), 'items' => [
                ['label' => Yii::t('module/editorjs', 'List'), 'url' => ['/' . $this->id.'/static']],                
            ]],            
        ];
    }
} // end class
