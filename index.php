<?php
include_once('DbHelper.php');
include_once('helpers/user.php');

date_default_timezone_set("Indian/Maldives");
header("Content-Type: application/json; charset=UTF-8");

if (!empty($_POST['task'])) {
    switch ($_POST['task']) {
        case 'user-create':
            exit(CreateUser());
            break;
        case 'user-login':
            exit(LoginUser());
            break;
        case 'user-update':
            exit(UpdateUser());
            break;
        case 'address-fetch':
            exit(FetchAddresses());
            break;
        default:
            exit(json_encode(['success' => false, 'code' => 1, 'message' => 'invalid task']));
            break;
    }
} else {
    exit(json_encode(['success' => false, 'code' => 1, 'message' => 'empty']));
}
