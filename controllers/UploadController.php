<?php
namespace x51\yii2\modules\editorjs\controllers;

use x51\yii2\modules\editorjs\classes\Compare;
use x51\yii2\modules\editorjs\classes\Image;
use yii\base\DynamicModel;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;
use \DomDocument;
use \DOMXPath;
use \Yii;

class UploadController extends Controller
{
    public function behaviors()
    {
        return [
            'bootstrap' => [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => '\yii\filters\AccessControl',
                'rules' => [                    
                    [
                        'allow' => true,
                        'roles' => ['admin', 'static_page_manager']
                    ]
                ]
            ],
            /*'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
        'file' => ['POST'],
        'url' => ['POST'],
        ],
        ],*/
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'url') { // только для actionUrl
            //$this->enableCsrfValidation = false;
            $request = Yii::$app->request;
            if (strpos($request->contentType, 'application/json') !== false) {
                if ($arParams = json_decode($request->rawBody, true)) { // получен запрос в виде json - занесем данные как будто это post запрос
                    foreach ($arParams as $n => $v) {
                        if (!isset($_POST[$n])) {
                            $_POST[$n] = $v;
                        }
                    }
                }
            }
        }
        return parent::beforeAction($action);
    }

    public function actionFile()
    {
        return $this->processUploadFile(UploadedFile::getInstanceByName('image'));

        /*$file = UploadedFile::getInstanceByName('image');
    if ($file) {
    $uploadRelDir = '/' . $this->module->uploadDir . '/' . $this->getUploadSubdir();

    //$uploadRelDir .= $uploadDirUpd;

    if (!$this->module->absUploadDir) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $uploadRelDir;
    if (!is_dir($uploadDir)) {
    mkdir($uploadDir);
    }
    } else {
    $uploadDir = $this->module->uploadDir;
    }

    $model = DynamicModel::validateData(
    ['file' => $file],
    [
    ['file', 'image'],
    ]
    );
    if ($model->hasErrors()) {
    // валидация завершилась с ошибкой
    return [
    'success' => 0,
    'errors' => $model->errors,
    ];
    } else {
    if ($file->baseName) {
    $baseFileName = mb_strtolower($file->baseName);
    } else {
    $baseFileName = time() . '-' . rand(1, 1000);
    }
    if ($file->extension) {
    $baseFileExt = mb_strtolower($file->extension);
    } else {
    $arExt = FileHelper::getExtensionsByMimeType($file->type);
    if ($arExt) {
    $baseFileExt = mb_strtolower(current($arExt));
    } else {
    $baseFileExt = 'tmp';
    }
    }
    $uploadFile = $uploadDir . $baseFileName . '.' . $baseFileExt;
    if (file_exists($uploadFile)) {
    $existBaseFileName = $baseFileName;
    $existUploadFile = $uploadFile;

    $baseFileName .= '-' . rand(1, 9999);
    $uploadFile = $uploadDir . $baseFileName . '.' . $baseFileExt;
    }

    // Валидация успешно выполнена
    $file->saveAs($uploadFile);
    if ($this->module->maxImageWidth) {
    $sizes = getimagesize($uploadFile);
    if ($sizes[0] > $this->module->maxImageWidth) { // уменьшить размер изображения
    $ratio = $sizes[0] / $this->module->maxImageWidth;
    $height = $sizes[1] * $ratio;
    Image::imageFileResize($uploadFile, $uploadFile, $this->module->maxImageWidth, 0);
    }
    }
    if (isset($existBaseFileName)) {
    // сравним файлы
    if (Compare::identicalFiles($uploadFile, $uploadDir . $existBaseFileName . '.' . $baseFileExt)) {
    // файлы одинаковые
    // удалим новую копию
    unlink($uploadFile);
    $baseFileName = $existBaseFileName;
    }
    }

    return [
    'success' => 1,
    'file' => [
    'url' => $uploadRelDir . $baseFileName . '.' . $baseFileExt,
    ],
    ];
    }
    file_put_contents(__DIR__ . '/out-upload-file.txt', print_r($file, true));
    }
     */

    }

    /**
     * Обработчик при передаче url картинки
     *
     * @return array
     */
    public function actionUrl()
    {
        $request = Yii::$app->request;

        if (Yii::$app->request->isPost) {
            $arPost = Yii::$app->request->post();
            if (isset($arPost['url'])) {
                if ($this->module->uploadImageFromUrl) {
                    $loaded = $this->loadImageFromUrl($arPost['url']);
                    return $this->processUploadFile($loaded);
                } else {
                    return [
                        'success' => 1,
                        'file' => [
                            'url' => $arPost['url'],
                        ],
                    ];
                }
            }
        }
        return [
            'success' => 0,
        ];
    } // end actionUrl

    /**
     * Обработка ссылки
     *
     * @return json
     */
    public function actionLink($url)
    {
        $result = ['success' => 0];
        /*
        [
        'success'=>1,
        'meta'=>[
        'title' => '',
        'description' => '',
        'image' => [
        'url' => ''
        ]
        ]
        ]
         */

        // получить содержимое страницы
        $page = $this->loadPageFromUrl($url);
        //Yii::debug($page);
        if ($page) {
            Yii::debug('Получена страница');
            // получить opengraph
            libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
            $doc = new DomDocument();
            $doc->loadHTML($page);
            $xpath = new DOMXPath($doc);
            $query = '//*/meta[starts-with(@property, \'og:\')]';
            $metas = $xpath->query($query);
            $rmetas = array();
            foreach ($metas as $meta) {
                $property = $meta->getAttribute('property');
                $content = $meta->getAttribute('content');
                $rmetas[$property] = $content;
            }
            Yii::debug($rmetas);
            // title description image
            if (!isset($rmetas['title'])) {
                $query = '//*/title';
                $metas = $xpath->query($query);
                $tmetas = array();
                foreach ($metas as $meta) {
                    $rmetas['title'] = $meta->textContent;
                }
            }
            Yii::debug($rmetas);

            $result = [
                'success' => 1,
                'meta' => [
                    'title' => !empty($rmetas['og:title']) ? $rmetas['og:title'] : '',
                    'description' => !empty($rmetas['og:description']) ? $rmetas['og:description'] : '',
                ],
            ];
            if (!empty($rmetas['og:image'])) {
                $result['meta']['image'] = [
                    'url' => $rmetas['og:image'],
                ];
            }
        }

        return $result;
    } // end actionLink

    /**
     * Получить содержимое страницы
     *
     * @param string $url
     * @return boolean|string
     */
    protected function loadPageFromUrl($url)
    {
        Yii::debug('Получение страницы ' . $url);
        try {
            $page = file_get_contents($url, false, $this->getContext());
        } catch (\Exception $e) {
            return false;
        }
        return $page;
    } // end loadPageFromUrl

    /**
     * Загружает картинку по url
     *
     * @param string $url
     * @return \yii\web\UploadedFile
     */
    protected function loadImageFromUrl($url)
    {
        $r = fopen($url, 'r', false, $this->getContext());
        if ($r) {
            $tempFilename = tempnam(sys_get_temp_dir(), 'ejs');
            Yii::debug($tempFilename);

            $tempImg = @fopen($tempFilename, 'w+');
            if ($tempImg) {
                while (!feof($r)) {
                    $buff = fread($r, 8192);
                    if ($buff) {
                        fwrite($tempImg, $buff);
                    }
                }
                fclose($tempImg);
                fclose($r);

                // оригинальное имя файла
                $name = substr($url, strrpos($url, '/') + 1);
                // определим тип
                $arImageFile = [
                    'name' => $name,
                    'tempName' => $tempFilename,
                    'size' => filesize($tempFilename),
                    'type' => \yii\helpers\FileHelper::getMimeType($tempFilename),
                    'error' => UPLOAD_ERR_OK,
                ];
                Yii::debug($arImageFile);
                return new UploadedFile($arImageFile);
            }
            fclose($r);
        }
        return null;
    } // end loadImageFromUrl

    /**
     * Возвращает контекст для запроса
     *
     * @return context
     */
    protected function getContext()
    {
        return stream_context_create([
            'http' => [
                'method' => 'GET',
            ],
        ]);
    }

    /**
     * Выполняет операцию над загруженным файлом (картинкой) по настройкам модуля
     *
     * @param UploadedFile $file
     * @return void
     */
    protected function processUploadFile(UploadedFile $file)
    {
        $res = [
            'success' => 0,
        ];

        if ($file) {
            // создаем динамическую модель для валидации картинки и выполняем ее
            $model = DynamicModel::validateData(
                ['file' => $file],
                [
                    ['file', 'image'],
                ]
            );
            if ($model->hasErrors()) {
                // валидация завершилась с ошибкой - это возможно не картинка
                $res = [
                    'success' => 0,
                    'errors' => $model->errors,
                ];
            } else {
                // валидация без ошибок
                // разберемся с имененм файла и его расширением - если расширения нет, то определим его по mime
                if ($file->baseName) {
                    $baseFileName = mb_strtolower($file->baseName);
                } else {
                    $baseFileName = time() . '-' . rand(1, 1000);
                }
                if ($file->extension) {
                    $baseFileExt = mb_strtolower($file->extension);
                } else {
                    $arExt = FileHelper::getExtensionsByMimeType($file->type);
                    if ($arExt) {
                        $baseFileExt = mb_strtolower(current($arExt));
                    } else {
                        $baseFileExt = 'tmp';
                    }
                }

                // каталог для сохранения загруженной картинки. если его нет - создать
                $actualUploadDir = $this->getUploadDir();
                if (!is_dir($actualUploadDir)) {
                    mkdir($actualUploadDir);
                }
                // проверим, чтобы имя сохраняемого файла было уникальным
                $uploadFile = $actualUploadDir . $baseFileName . '.' . $baseFileExt;
                if (file_exists($uploadFile)) {
                    $existBaseFileName = $baseFileName;
                    $existUploadFile = $uploadFile;

                    $baseFileName .= '-' . rand(1, 9999);
                    $uploadFile = $actualUploadDir . $baseFileName . '.' . $baseFileExt;
                }

                Yii::debug([
                    $uploadFile,
                    $file->tempName,
                    file_exists($file->tempName),
                    $file->baseName,
                    $file->name,

                ]);

                // Валидация успешно выполнена

                //$resSaveAs = $file->saveAs($uploadFile);
                // перенесм загруженный файл в каталог со сформированным именем
                $this->moveTempFile($file->tempName, $uploadFile);

                Yii::debug([
                    file_exists($uploadFile),
                    $uploadFile,
                    $file->tempName,
                    file_exists($file->tempName),
                ]);

                // тест на максимальную ширину картинки
                if ($this->module->maxImageWidth) {
                    $sizes = getimagesize($uploadFile);
                    if ($sizes[0] > $this->module->maxImageWidth) { // уменьшить размер изображения
                        $ratio = $sizes[0] / $this->module->maxImageWidth;
                        $height = $sizes[1] * $ratio;
                        Image::imageFileResize($uploadFile, $uploadFile, $this->module->maxImageWidth, 0);
                    }
                }
                // если файл был с не уникальным именем, то проверим 2 файла на идентичность
                if (isset($existBaseFileName)) {
                    // сравним файлы
                    if (Compare::identicalFiles($uploadFile, $this->getUploadDir() . $existBaseFileName . '.' . $baseFileExt)) {
                        // файлы одинаковые
                        // удалим новую копию
                        unlink($uploadFile);
                        $baseFileName = $existBaseFileName;
                    }
                }

                $res = [
                    'success' => 1,
                    'file' => [],
                ];

                if ($this->module->absUploadDir) {
                    $fn = $this->getUploadSubdir() . $baseFileName . '.' . $baseFileExt;
                    $res['file']['url'] = Url::to([
                        'get-image',
                        'relfile' => $fn,
                    ]);
                } else {
                    $res['file']['url'] = '/' . $this->module->uploadDir . '/' . $this->getUploadSubdir() . $baseFileName . '.' . $baseFileExt;
                }
            }

        }
        return $res;
    } // end processUploadFile

    /**
     * По настройкам формирует относительное имя директории для сохранения.
     *
     * @return string
     */
    protected function getUploadSubdir()
    {
        $uploadDirUpd = '';
        if ($this->module->useDateDir) {
            $uploadDirUpd = date("Y-m-d");
        }
        if ($this->module->useUserIdDir && !empty(Yii::$app->user->identity)) {
            if ($uploadDirUpd) {
                $uploadDirUpd .= '-';
            }
            $uploadDirUpd .= Yii::$app->user->id;
        }
        if ($uploadDirUpd) {
            $uploadDirUpd .= '/';
        }
        return $uploadDirUpd;
    }

    /**
     * Возвращает абсолютный путь к директории для сохранения
     *
     * @return string
     */
    protected function getUploadDir()
    {
        if ($this->module->absUploadDir) {
            return $this->module->uploadDir . '/' . $this->getUploadSubdir();
        } else {
            return $_SERVER['DOCUMENT_ROOT'] . '/' . $this->module->uploadDir . '/' . $this->getUploadSubdir();
        }
    } // end getUploadDir

    /**
     * Переносит содержимое временного файла. Временный файл удаляется.
     *
     * @param string $tempFile
     * @param string $destFile
     * @return boolean
     */
    protected function moveTempFile($tempFile, $destFile)
    {
        $result = false;
        if (file_exists($tempFile)) {
            if (copy($tempFile, $destFile)) {
                $result = true;
            }
            unlink($tempFile);
        }
        return $result;
    }

} // end class
