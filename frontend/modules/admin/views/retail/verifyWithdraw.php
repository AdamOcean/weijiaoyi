<?php use common\helpers\Hui; ?>
<h2 style="text-align: center"><?= $model->retail->account ?>，余额<?= $model->retail->total_fee ?></h2>
<?php $form = self::beginForm() ?>
<?= $form->field($model->retail->adminAccount, 'bank_name')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->retail->adminAccount, 'bank_card')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->retail->adminAccount, 'bank_address')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->retail->adminAccount, 'bank_user')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->retail->adminAccount, 'bank_mobile')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model, 'amount')->textInput(['disabled' => 'disabled']) ?>
<div class="row cl" style="margin: 0 auto;padding-left: 40%">
<?= Hui::successBtn('确认', null, ['id' => 'submitBtn', 'class' => 'size-L']) ?>
<?= Hui::dangerBtn('失败否决', null, ['id' => 'denyBtn', 'class' => 'size-L', 'style' => ['margin-left' => '20px']]) ?>
</div>
<?php self::endForm() ?>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $.confirm('确认出金 ' + <?= $model->amount ?> + ' 元？', function () {
            $("form").ajaxSubmit($.config('ajaxSubmit', {
                data: {state: <?= $model::OP_STATE_PASS ?>},
                success: function (msg) {
                    if (msg.state) {
                        $.alert(msg.info || '操作成功', function () {
                            parent.location.reload();
                        })
                    } else {
                        $.alert(msg.info);
                    }
                }
            }));
        });
        return false;
    });

    $("#denyBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            data: {state: <?= $model::OP_STATE_DENY ?>},
            success: function (msg) {
                if (msg.state) {
                    $.alert(msg.info || '操作成功', function () {
                        parent.location.reload();
                    })
                } else {
                    $.alert(msg.info);
                }
            }
        }));
    });
});
</script>