<?php
include_once('DbHelper.php');

date_default_timezone_set("Indian/Maldives");
header("Content-Type: application/json; charset=UTF-8");

if (!empty($_POST['task'])) {
    switch ($_POST['task']) {
        case 'user-create':
            exit(CreateUser());
            break;
        case 'user-login':
            exit(Login());
            break;
        case 'user-fetch':
            exit(json_encode(['success' => true, 'message' => 'users fetched', 'payload' => FetchUser()]));
            break;
        case 'user-update':
            exit(UpdateUser());
            break;
        case 'user-delete':
            exit(DeleteUser());
            break;
        default:
            exit(json_encode(['success' => false, 'code' => 1, 'message' => 'invalid task']));
            break;
    }
} else {
    exit(json_encode(['success' => false, 'code' => 1, 'message' => 'empty']));
}

// User
function CheckUser()
{
    $user = GetUser($_POST['hash']);
    if ($user == null)
        exit(json_encode(['success' => false, 'message' => 'Invalid User']));
    else
        return $user;
}

function CreateUser()
{
    $data = db_query(
        'select * from users where username=?',
        's',
        $_POST['username']
    );

    if ($data->num_rows > 0) {
        exit(json_encode(['success' => false, 'message' => 'username already exists']));
    }

    $hash = md5($_POST['username']);
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    db_query(
        'insert into users (hash, fullname, username, passwordHash, isAdmin) values (?,?,?,?,?)',
        'sssss',
        $hash,
        $_POST['fullname'],
        $_POST['username'],
        $passwordHash,
        $_POST['isAdmin']
    );

    exit(json_encode(['success' => true, 'message' => 'user created', 'payload' => GetUser($hash)]));
}

function FetchUser()
{
    $data = db_query(
        'select * from users where isAdmin=1 and active=1 union select * from users where companyId=? and active=1',
        's',
        $_POST['companyId']
    );

    return $data->fetch_all(MYSQLI_ASSOC);
}

function UpdateUser()
{
    $user = CheckUser();

    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $data = db_query(
        'update users set fullname=?, passwordHash=?, isAdmin=? where id=?',
        'ssss',
        $_POST['fullname'],
        $passwordHash,
        $_POST['isAdmin'],
        $user['id']
    );

    exit(json_encode(['success' => true, 'message' => 'user updated', 'payload' => FetchUser()]));
}

function DeleteUser()
{
    $user = CheckUser();

    $data = db_query(
        'update users set active=0 where id=?',
        's',
        $user['id'],
    );

    exit(json_encode(['success' => true, 'message' => 'user deleted']));
}

function GetUser($hash)
{
    $data = db_query(
        'select * from users where hash=?',
        's',
        $hash
    );

    if ($data->num_rows > 0)
        return $data->fetch_assoc();
    else
        return null;
}

function GetUserByUsername($username)
{
    $data = db_query(
        'select * from users where username=?',
        's',
        $username
    );

    if ($data->num_rows > 0)
        return $data->fetch_assoc();
    else
        return null;
}

function Login()
{
    if (empty($_POST['hash'])) {
        $username = $_POST['username'];

        $user = GetUserByUsername($username);

        if ($user == null) {
            exit(json_encode(['success' => false, 'code' => 0, 'message' => 'User not registered']));
        }

        if (!password_verify($_POST['password'], $user['passwordHash'])) {
            exit(json_encode(['success' => false, 'code' => 0, 'message' => 'Incorrect password']));
        }

        $_POST['hash'] = $user['hash'];
    } else {
        $user = GetUser($_POST['hash']);

        if ($user == null) {
            exit(json_encode(['success' => false, 'code' => 3, 'message' => 'Invalid Hash']));
        }
    }

    // $user['accounts'] = FetchAccounts();
    // $user['categories'] = FetchCategories();
    // $user['transactions'] = FetchTransactions();
    // $user['overallTransactions'] = FetchOverallTransactions();

    // if ($user['isAdmin'] == true) {
    //     $user['userAccounts'] = db_query('select * from users where active=1 order by fullname')->fetch_all(MYSQLI_ASSOC);
    // }

    exit(json_encode(['success' => true, 'code' => 1, 'message' => 'logged in', 'payload' => $user]));
}
