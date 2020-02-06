<?php
require_once('../header.php');


$resource_id = isset($_POST['resource_id']) ? $_POST['resource_id'] : '';
$token = isset($_POST['token']) ? $_POST['token'] : '';
$user_identity = isset($_POST['user_identity']) ? $_POST['user_identity'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

/// 1. 检查用户标识是否合法
if (!user_identity_verify($user_identity)) {
    apiout(-2, "非法操作（刷票？）");
}

/// 2. 检查谷歌 reCAPTCHA
$err = '';
$score = recaptcha_verify($token, $err);
if ($score === false || $score === null) {
    apiout(-10, "校验失败: "  . $err);
} else if ($score < 0.5) {
    apiout(-11, "你可能是机器人？");
}

/// 3. 执行投票操作
if ($action == 'vote') {
    /// 3.A 投票
    resource_vote($resource_id, $user_identity, $score);
} else if ($action == 'unvote') {
    /// 3.B 撤销投票
    resource_unvote($resource_id, $user_identity);
}
else {
    apiout(-1, "非法操作({$action})");
}

$resource = get_by_resource_id($resource_id);

apiout(0, '', [
    'resource' => [
        'vote_score' => $resource['vote_score'],
        'resource_id' => $resource['resource_id'],
    ],
]);
    
?>
