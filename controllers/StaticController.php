<?php
namespace x51\yii2\modules\editorjs\controllers;

use yii\web\Controller;
use \x51\yii2\modules\editorjs\models\StaticPage;
use \Yii;
use \yii\data\ArrayDataProvider;
use \yii\helpers\FileHelper;

class StaticController extends Controller
{
    public function init() {        
        parent::init();
    }
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => '\yii\filters\AccessControl',
                'rules' => [                    
                    [
                        'allow' => true,
                        'roles' => ['static_page_manager']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['admin']
                    ]
                ]
            ],
        ];
    } // end behaviors
    
    public function actionIndex()
    {
        /*$beh = $this->getBehaviors();
foreach ($beh as $id => $b) {
    echo "$id<br>";
    print_r($b->rules);
}
echo '<pre>';
//print_r($this->getBehaviors());*/

        
        $ext = '.json';
        $ext_len = strlen($ext);
        $params = [
            'arFiles' => [],
            'allModels' => [],
            'ext' => '.json',
            'ext_len' => strlen('.json'),
        ];

        if ($pagesDir = $this->getStaticPageDir()) {
            $params['arFiles'] = FileHelper::findFiles($pagesDir, ['filter' => function ($path) use ($params) {
                if (is_dir($path)) {
                    return true;
                }
                if (strpos($path, $params['ext']) == strlen($path) - $params['ext_len']) {
                    return true;
                }
                return false;
            }]);

            $lenPagesDir = strlen($pagesDir);
            foreach ($params['arFiles'] as $fn) {

                $shortName = substr($fn, $lenPagesDir, strlen($fn) - $lenPagesDir - $params['ext_len']);
                if (substr($shortName, 0, 1) == DIRECTORY_SEPARATOR) {
                    $shortName = substr($shortName, 1);
                }

                $params['allModels'][] = new StaticPage($pagesDir, $params['ext'], $shortName);
            }
        }

        $params['dataProvider'] = new ArrayDataProvider([
            'key' => 'name',
            'allModels' => $params['allModels'],
            'modelClass' => '\\x51\\yii2\\modules\\editorjs\\models\\StaticPage',
        ]);
        return $this->render('index', $params);
    } // end actionIndex

    public function actionCreate()
    {

        if ($pagesDir = $this->getStaticPageDir()) {

            $model = new \x51\yii2\modules\editorjs\models\StaticPage($pagesDir, $this->module->extWithDot, '');
            $params = [
                'model' => $model,
            ];

            $rq = Yii::$app->request;
            if ($rq->isPost && $rq->post('operation') == 'create') {
                if ($name = $rq->post('name')) {

                    // проверка имени и контента

                    $model['name'] = $name;
                    $model['content'] = $rq->post('content');

                    if ($model->save()) {
                        $params['operation_result'] = true;
                    } else {
                        $params['operation_result'] = false;
                    }
                }
            }

            return $this->render('create', $params);
        }
        return $this->render('fail', []);
    } // end actionCreate

    public function actionUpdate($id)
    {
        if ($pagesDir = $this->getStaticPageDir()) {

            $model = new \x51\yii2\modules\editorjs\models\StaticPage($pagesDir, $this->module->extWithDot, $id);
            $params = [
                'id' => $id,
                'model' => $model,
            ];

            $rq = Yii::$app->request;
            if ($rq->isPost && $rq->post('operation') == 'update') {
                if ($name = $rq->post('name')) {
                    $model['name'] = $name;
                    // !!!проверка контента
                    $model['content'] = $rq->post('content');

                    if ($model->save()) {
                        $params['operation_result'] = true;
                    } else {
                        $params['operation_result'] = false;
                    }
                }
            }
            return $this->render('update', $params);
        }
        return $this->render('fail', []);
    } // end actionUpdate

    public function actionView($id) {
        if ($pagesDir = $this->getStaticPageDir()) {
            $model = new \x51\yii2\modules\editorjs\models\StaticPage($pagesDir, $this->module->extWithDot, $id);
            $params = [
                'model' => $model
            ];
            return $this->render('view', $params);
        }
    } // end actionView

    public function actionDelete($id) {
        if ($pagesDir = $this->getStaticPageDir()) {
            $model = new \x51\yii2\modules\editorjs\models\StaticPage($pagesDir, $this->module->extWithDot, $id);
            $model->delete();
            Yii::$app->response->redirect(['/'.$this->module->id.'/'.$this->id.'/index']);
        }
        return $this->render('fail', []);
    }

    protected function getStaticPageDir()
    {
        if ($pagesDir = $this->module->staticPageDir) {
            $pagesDir = FileHelper::normalizePath(Yii::getAlias($pagesDir));

            if (is_dir($pagesDir)) {
                return $pagesDir;
            } else {
                Yii::debug('Not found ' . $pagesDir);
            }
        }
        return false;
    } // end getStaticPageDir

    protected function validateContent($content)
    {
        return true;
    }

    protected function validateStaticFile($filename)
    {
        if (is_dir($filename)) {
            return true;
        }
        return false;
    }

} // end class
