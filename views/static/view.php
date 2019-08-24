<?php
use \Yii;

$this->title = Yii::t('module/editorjs/static', 'View: {name}', [
    'name' => $model['name']
]);
$this->params['breadcrumbs'][] = $this->context->module->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('module/editorjs/static', 'Static pages index'), 'url'=>['/'.$this->context->module->id.'/static']];
$this->params['breadcrumbs'][] = $this->title;

?>
<h1><?=$this->title?></h1>
<?php
if ($model->exists()) {
    \x51\yii2\modules\editorjs\assets\RenderAssets::register($this);    
    echo '<div class="editorjs-render-content">';
    echo $this->context->module->renderFromJsonToHtml($model['content']);
    echo '</div>';
} else {
    echo '<div class="alert alert-warning">'.Yii::t('module/editorjs/static', 'Static page not found').'</div>';
}