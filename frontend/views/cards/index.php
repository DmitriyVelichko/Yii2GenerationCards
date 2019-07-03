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

                <span style="float: right;">Просмотров (<?=!empty($card->countsViews) ? $card->countsViews : 0?>)</span>
            </h3>
        </div>

        <div class="panel-body">
            <img align="left" style="float:left; margin: 4px 10px 2px 0px; border:1px solid #CCC; padding:6px;" src="<?=Yii::getAlias('@cardsImgUrl').'/'.$card->image?>" />
            <?=$card->description?>
        </div>
    </div>

    <?endforeach;?>

<?=LinkPager::widget(['pagination' => $pages])?>
<?endif;?>
