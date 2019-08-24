<?php
use \yii\widgets\ActiveForm;
use \yii\widgets\Pjax;
use \yii\helpers\Html;
 

$formId = 'staticpageform';

$jsonContent = '';
if ($savedContent = $model['content']) {
    json_decode($savedContent);
    if (json_last_error() == JSON_ERROR_NONE) {
        $jsonContent = $savedContent;
    }
    unset($savedContent);
}

?>
<?php
Pjax::begin(['id' => 'pjstaticpage', 'enablePushState' => false]);
?>
<?php
if (!empty($operation_result)) {
    echo '<div class="alert alert-'.($operation_result ? 'success' : 'warning').' auto-class" role="alert" data-auto-class="hide" data-auto-class-timer="5000">'.($operation_result ? Yii::t('module/editorjs/static', 'Operation success') : Yii::t('module/editorjs/static', 'Warning! Operation error')).'</div>';    
}

$form = ActiveForm::begin([
    'options' => [
        'data-pjax' => true,
        'id' => $formId,
    ],
]);
if (!empty($action)) {
    echo Html::hiddenInput('operation', $action);
}
?>

<?php
echo $form->field($model, 'name')->textInput(['maxlength' => true, 'name'=>'name']);
$this->context->module->editorjs('edit-content', $formId, 'content', $jsonContent);
?>
<?php
echo Html::submitButton(
    Yii::t('module/editorjs', 'Save'),
    ['class' => 'btn btn-success', 'name' => 'submit', 'value' => $formId]
);
?>

<?php
ActiveForm::end();
Pjax::end();
?>