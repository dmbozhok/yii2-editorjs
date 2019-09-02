<?php
namespace x51\yii2\modules\editorjs\actions;

use yii\base\Action;
use yii\base\ViewNotFoundException;
use \Yii;
use \yii\web\NotFoundHttpException;

class StaticPageAction extends Action
{
    const EVENT_BEFORE_RUN = 'beforeStaticPageActionRun';

    /**
     * @var string the name of the GET parameter that contains the requested view name.
     */
    public $pageIdParam = 'page';
    /**
     * @var string the name of the default view when [[\yii\web\ViewAction::$pageIdParam]] GET parameter is not provided
     * by user. Defaults to 'index'. This should be in the format of 'path/to/view', similar to that given in the
     * GET parameter.
     * @see \yii\web\ViewAction::$pagePrefix
     */
    public $defaultPageId = 'index';
    /**
     * @var string a string to be prefixed to the user-specified view name to form a complete view name.
     * For example, if a user requests for `tutorial/chap1`, the corresponding view name will
     * be `pages/tutorial/chap1`, assuming the prefix is `pages`.
     * The actual view file is determined by [[\yii\base\View::findViewFile()]].
     * @see \yii\base\View::findViewFile()
     */
    public $pagePrefix = '';
    /**
     * @var mixed the name of the layout to be applied to the requested view.
     * This will be assigned to [[\yii\base\Controller::$layout]] before the view is rendered.
     * Defaults to null, meaning the controller's layout will be used.
     * If false, no layout will be applied.
     */
    public $layout;

    public $editorjsModuleName;

    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws NotFoundHttpException if the view file cannot be found
     */
    public function run()
    {
        $pageId = $this->resolvePageId();

        $eventBefore = new \yii\base\ActionEvent($this, ['data' => [
            'pageId' => $pageId,
        ]]);
        $this->trigger(self::EVENT_BEFORE_RUN, $eventBefore);

        if ($eventBefore->isValid) {

            $this->controller->actionParams[$this->pageIdParam] = Yii::$app->request->get($this->pageIdParam);

            $controllerLayout = null;
            if ($this->layout !== null) {
                $controllerLayout = $this->controller->layout;
                $this->controller->layout = $this->layout;
            }

            try {
                $output = $this->render($pageId);

                if ($controllerLayout) {
                    $this->controller->layout = $controllerLayout;
                }
            } catch (ViewNotFoundException $e) {
                if ($controllerLayout) {
                    $this->controller->layout = $controllerLayout;
                }

                if (YII_DEBUG) {
                    throw new NotFoundHttpException($e->getMessage());
                }

                throw new NotFoundHttpException(
                    Yii::t('yii', 'The requested view "{name}" was not found.', ['name' => $pageId])
                );
            }

            return $output;
        }
        return '';
    }

    /**
     * Renders a view.
     *
     * @param string $pageId view name
     * @return string result of the rendering
     */
    protected function render($pageId)
    {
        if ($this->editorjsModuleName) {
            $editorjsModule = Yii::$app->getModule($this->editorjsModuleName);
            if ($editorjsModule instanceof \x51\yii2\modules\editorjs\Module) {
                
                $arBlocks = $editorjsModule->getStaticPageBlocksArray($pageId);

                if ($arBlocks) {
                    // не визуальные настройки
                    foreach ($arBlocks as $block) {
                        switch ($block['type']) {
                            case 'header': {
                                if ($block['data']['level'] == 1) {
                                    //Yii::$app->view->title = htmlspecialchars(strip_tags($block['data']['text']));
                                }
                                break;
                            }
                        }
                    }
                    return $this->controller->renderContent('<div class="editorjs-render-content">' . $editorjsModule->renderStaticPage($pageId) . '</div>');
                }
                return $this->controller->renderContent('<div class="editorjs-render-content"></div>');
            } else {
                throw new NotFoundHttpException(Yii::t('module/editorjs/static', 'Module {name} not implement \x51\yii2\modules\editorjs\Module', ['name' => $this->editorjsModuleName]));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('module/editorjs/static', 'Not set editorjsModuleName option'));
        }
    }

    /**
     * Resolves the view name currently being requested.
     *
     * @return string the resolved view name
     * @throws NotFoundHttpException if the specified view name is invalid
     */
    protected function resolvePageId()
    {
        $pageId = Yii::$app->request->get($this->pageIdParam, $this->defaultPageId);

        if (!is_string($pageId) || !preg_match('~^\w(?:(?!\/\.{0,2}\/)[\w\/\-\.])*$~', $pageId)) {
            if (YII_DEBUG) {
                throw new NotFoundHttpException("The requested view \"$pageId\" must start with a word character, must not contain /../ or /./, can contain only word characters, forward slashes, dots and dashes.");
            }

            throw new NotFoundHttpException(Yii::t('yii', 'The requested view "{name}" was not found.', ['name' => $pageId]));
        }

        return empty($this->pagePrefix) ? $pageId : $this->pagePrefix . '/' . $pageId;
    }
}
