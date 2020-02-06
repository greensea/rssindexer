<?php
require_once('../header.php');

$token = $_POST['token'] ?? '';

$err = null;
$score = recaptcha_verify($token, $err);
if (!$score || $score < 0.5) {
    apiout(-1, "reCAPTCHA 验证失败({$score}): {$err}");
}

$id = user_identity_new();

apiout(0, '', [
    'user_identity' => $id,
]);


?>
