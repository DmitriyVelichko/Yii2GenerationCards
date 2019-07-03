<?php

Yii::setAlias('@cardsImgPath', $_SERVER['DOCUMENT_ROOT'].'/images/cards');
Yii::setAlias('@cardsImgUrl', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/images/cards');

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
];
