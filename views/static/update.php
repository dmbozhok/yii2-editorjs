<?php
use \Yii;

$this->title = Yii::t('module/editorjs/static', 'Update: {name}', [
    'name' => $model['name'],
]);
$this->params['breadcrumbs'][] = $this->context->module->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('module/editorjs/static', 'Static pages index'), 'url' => ['/' . $this->context->module->id . '/static']];
$this->params['breadcrumbs'][] = $this->title;


$dir = $this->context->module->staticPageDir;
$ext = $this->context->module->extWithDot;
?>
<h1><?=$this->title?></h1>
<?php
if ($dir && $ext) {
    $params = [
        'model' => $model,
        'action' => 'update',
    ];
    if (!empty($operation_result)) {
        $params['operation_result'] = $operation_result;
    }
    echo $this->render('_form', $params);
}