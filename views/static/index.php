<?php
use yii\grid\GridView;
use \yii\helpers\Html;

$this->params['breadcrumbs'][] = $this->context->module->id;
$this->title = Yii::t('module/editorjs/static', 'Static pages: Index');
$this->params['breadcrumbs'][] = $this->title;


?>
<h1><?=$this->title?></h1>
<div class="top-buttons">
<?= Html::a(Yii::t('module/editorjs/static', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
</div>
<?=GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'name' => [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'name',
            'label' => 'Name',
            /*'content' => function ($model, $key, $index, $column) {
                return $model->name;
            },*/
        ],
        [
            'class' => 'yii\grid\ActionColumn',
        ]
    ],
])?>
