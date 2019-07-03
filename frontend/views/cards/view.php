<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?=$card->name?>
        </h3>
    </div>

    <div class="panel-body">
        <?=$card->description?>
        <img width="100" height="60" align="left" style="float:left; margin: 4px 10px 2px 0px; border:1px solid #CCC; padding:6px;" src="<?=Yii::getAlias('@cardsImgUrl').'/'.$card->image?>" />
    </div>

</div>