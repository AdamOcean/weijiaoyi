<?php $form = self::beginForm() ?>
<?= $model->title('特殊产品') ?>
<?= $form->field($model, 'table_name')->textInput(['placeholder' => '产品的全拼音']) ?>
<?= $form->field($model, 'name') ?>
<?= $form->field($productParam, 'start_price')->textInput(['placeholder' => '开始']) ?>
<?= $form->field($productParam, 'end_price')->textInput(['placeholder' => '结束']) ?>
<?= $form->field($productParam, 'start_point')->textInput(['placeholder' => '开始']) ?>
<?= $form->field($productParam, 'end_point')->textInput(['placeholder' => '结束']) ?>
<?= $form->submit($model) ?>
<?php self::endForm() ?>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (msg.state) {
                    $.alert(msg.info || '操作成功', function () {
                        parent.location.reload();
                    });
                } else {
                    $.alert(msg.info);
                }
            }
        }));
        return false;
    });
});
</script>