<?php
include_once(realpath('DbHelper.php'));
header("Content-Type: application/json; charset=UTF-8");

function CheckUser()
{
    $user = GetUserByHash($_POST['hash']);
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

    $hash = md5(uniqid(rand(), true));
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    db_query(
        'INSERT INTO users (hash, name, address_id, mobile, password_hash) VALUES (?, ?, ?, ?, ?)',
        'ssiss',
        $hash,
        $_POST['name'],
        $_POST['address_id'],
        $_POST['mobile'],
        $passwordHash
    );

    exit(json_encode(['success' => true, 'message' => 'user created', 'payload' => GetUserByHash($hash)]));
}

function GetUserById($id)
{
    $data = db_query(
        'select * from users where user_id=?',
        's',
        $id
    );

    if ($data->num_rows > 0)
        return $data->fetch_assoc();
    else
        return null;
}

function GetUserByMobile($mobile)
{
    $data = db_query(
        'select * from users where mobile=?',
        's',
        $mobile
    );

    if ($data->num_rows > 0)
        return $data->fetch_assoc();
    else
        return null;
}

function GetUserByHash($hash)
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

function UpdateUser()
{
    CheckUser();

    db_query(
        'UPDATE users SET name = ?, address_id = ?, mobile = ? WHERE hash = ?',
        'siss',
        $_POST['name'],
        $_POST['address_id'],
        $_POST['mobile'],
        $_POST['hash']
    );

    exit(json_encode(['success' => true, 'message' => 'user updated', 'payload' => GetUserByHash($_POST['hash'])]));
}

function UpdateUserPassword()
{
    $user = CheckUser();

    if (!password_verify($_POST['password'], $user['password_hash'])) {
        exit(json_encode(['success' => false, 'code' => 0, 'message' => 'Incorrect password']));
    }

    db_query(
        'UPDATE users SET password_hash = ? WHERE hash = ?',
        'ss',
        password_hash($_POST['new_password'], PASSWORD_DEFAULT),
        $_POST['hash']
    );

    exit(json_encode(['success' => true, 'message' => 'user updated', 'payload' => GetUserByHash($_POST['hash'])]));
}

function LoginUser()
{
    if (empty($_POST['hash'])) {
        $user = GetUserByMobile($_POST['mobile']);

        if ($user == null) {
            exit(json_encode(['success' => false, 'code' => 0, 'message' => 'User not registered']));
        }

        if (!password_verify($_POST['password'], $user['password_hash'])) {
            exit(json_encode(['success' => false, 'code' => 0, 'message' => 'Incorrect password']));
        }
    } else {
        $user = GetUserByHash($_POST['hash']);

        if ($user == null) {
            exit(json_encode(['success' => false, 'code' => 3, 'message' => 'Invalid Hash']));
        }
    }

    exit(json_encode(['success' => true, 'code' => 1, 'message' => 'logged in', 'payload' => $user]));
}
