<?php

Yii::setAlias('@cardsImgPath', $_SERVER['DOCUMENT_ROOT'].'/backend/images');
Yii::setAlias('@cardsImgUrl', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/backend/images');

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
];
