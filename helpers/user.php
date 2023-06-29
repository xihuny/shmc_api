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
        'select * from users where mobile=?',
        's',
        $_POST['mobile']
    );

    if ($data->num_rows > 0) {
        exit(json_encode(['success' => false, 'message' => 'mobile already registered']));
    }

    $hash = md5(uniqid(rand(), true));
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $otp = mt_rand(1000, 9999);

    db_query(
        'INSERT INTO users (hash, mobile, password_hash, otp) VALUES (?, ?, ?, ?)',
        'sssi',
        $hash,
        $_POST['mobile'],
        $passwordHash,
        $otp
    );

    // send otp

    exit(json_encode(['success' => true, 'message' => 'user created', 'payload' => GetUserByHash($hash)]));
}

function VerifyMobile()
{
    if (VerifyOtp()) {
        db_query(
            'update users set account_status=1 where hash=? and otp=?',
            'si',
            $_POST['hash'],
            $_POST['otp']
        );

        exit(json_encode(['success' => true, 'message' => 'verification success']));
    } else {
        exit(json_encode(['success' => false, 'message' => 'verification failed']));
    }
}

function VerifyOtp()
{
    $data = db_query(
        'select * from users where hash=? and otp=?',
        'si',
        $_POST['hash'],
        $_POST['otp']
    );

    return $data->num_rows > 0;
}

function ForgotPassword()
{
    if (empty($_POST['new_password'])) {
        $data = db_query(
            'select * from users where mobile=?',
            's',
            $_POST['mobile']
        );

        if ($data->num_rows > 0) {
            $otp = mt_rand(1000, 9999);

            db_query(
                'update users set otp=? where mobile=?',
                'is',
                $otp,
                $_POST['mobile']
            );

            // sent otp

            $user = $data->fetch_assoc();
            exit(json_encode(['success' => true, 'message' => 'otp sent to reset mobile', 'payload' => GetUserByHash($user['hash'])]));
        } else {
            exit(json_encode(['success' => false, 'message' => 'invalid mobile number']));
        }
    } else {
        UpdateUserPassword();
    }
}

function GetUserById($id)
{
    $data = db_query(
        'select hash, mobile, account_status, password_hash from users where user_id=?',
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
        'select hash, mobile, account_status, password_hash from users where mobile=?',
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
        'select hash, mobile, account_status, password_hash from users where hash=?',
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

    if (VerifyOtp()) {
        // if (!password_verify($_POST['password'], $user['password_hash'])) {
        //     exit(json_encode(['success' => false, 'code' => 0, 'message' => 'Incorrect password']));
        // }

        db_query(
            'UPDATE users SET password_hash = ? WHERE hash = ?',
            'ss',
            password_hash($_POST['new_password'], PASSWORD_DEFAULT),
            $_POST['hash']
        );

        exit(json_encode(['success' => true, 'message' => 'user updated', 'payload' => GetUserByHash($_POST['hash'])]));
    } else {
        exit(json_encode(['success' => false, 'code' => 0, 'message' => 'invalid otp code']));
    }
}

function LoginUser()
{
    if (empty($_POST['hash'])) {
        $user = GetUserByMobile($_POST['mobile']);

        if ($user == null) {
            exit(json_encode(['success' => false, 'code' => 0, 'message' => 'User not registered']));
        }

        if (!password_verify($_POST['password'], $user['password_hash'])) {
            exit($user['password_hash']);
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
