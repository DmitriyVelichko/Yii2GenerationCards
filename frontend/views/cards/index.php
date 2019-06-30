<?php
use \yii\helpers\Url;
use \yii\widgets\LinkPager;
?>

<?php if(!empty($cards)): ?>

    <?php foreach ($cards as $card): ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <a href="<?=Url::to(['cards/view', 'id' => $card->id])?>">
                    <?=$card->name?>
                </a>
            </h3>
        </div>

        <div class="panel-body">
            <?=$card->description?>
        </div>
    </div>

    <?endforeach;?>

<?=LinkPager::widget(['pagination' => $pages])?>
<?endif;?>
