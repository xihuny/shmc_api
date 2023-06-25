<?php
include_once(realpath('DbHelper.php'));

function FetchAddresses()
{
    $data = db_query(
        'select * from addresses where active=1'
    );

    return $data->fetch_all(MYSQLI_ASSOC);
}
