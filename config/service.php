<?php

return [
    // API-ID => [
    //     'url'    => API-URL,          // 请求URL，省略前缀 '/' 号，所有URL字符请不要使用大写字母
    //     'method' => API-METHOD,       // GET、POST、PUT、PATCH、DELETE
    //     'level'  => SECRET-LEVEL,     // level可参考 CheckSecurity 中间件
    //     'auth'   => AUTHORIZED-USERS, // 授权用户列表，不同用户以 ',' 号分割
    // ],

    1 => ['url' => 'demo', 'method' => 'POST', 'level' => 3, 'auth' => 'user1,user2'],
    2 => ['url' => 'test/cesi-demo', 'method' => 'POST', 'level' => 3, 'auth' => 'user1,user2'],
];
