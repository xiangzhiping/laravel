<?php
/**
 * User: xiangzhiping
 * Date: 2018/7/5
 */

/**
 * 生成openssl 私钥和公钥
 * openssl genrsa -out rsa_private_key.pem 1024   生成原始 RSA私钥文件
 * openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out private_key.pem  将原始 RSA私钥转换为 pkcs8格式
 * openssl rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem  生成RSA公钥文件
 */
return [

    // HSxxx 算法加密key
    'JWT_KEY'              => env('JWT_KEY', md5('xzp')),

    // RSxxx 算法加密私钥和公钥
    'JWT_PRIVATE_KEY'      => env('JWT_PRIVATE_KEY', 'MIICWwIBAAKBgQDLpzUE8tzxX6JNjh/JdAkwRLZGI4k43aPf9iQG4xUecRHRfra1nv0LSygN6JTzFIBDLavO02olfwKbK/dniNB1/8RN4B1IiU3Ws5XOgpIfOiSiUP7ztVcyzzdETbIg3uDA8UVEkUd4qZTbIO5cdqtVvoVvSJqIIKI5mFAGvA3drwIDAQABAoGASbY+uYjSMceErn+Xv8nxHXhxZRetrliC3ShxR9DfrwIMCgDMTxa5zmGooMgjSYyNFEOuoRDipanAHrweM2pQ0Q+roMj08ml/sDvHD6dcCW5c0/7FkR5oec+fwTKLK4Onj6a6aX7N+HApTsjMKFY1k9TuRF6FvSLG1e2UWsyDPRkCQQDpKPLEq0rF4z+zhdA79hko1mZLu1HDo0qzZ3dztEqywbzfRCGhx6domGkxlUVK+yuzDwKXqs9wHVVaS140OZC7AkEA35pN2+SyYCN2OvZ0Vk33bd3qvRSUghsTt/IyVmNu3Hl2Hk6JAU/jDzcv6xt6ENPU7r5iGzoVNzSD168otcghnQJABUeUF7f5PXnj22DQUktETpRsRJK9SzzLWDyji88tUdbpO/UC/fLTa57n4uOKaiQ93RQd7ulDQTqo0B6cx3n2OQJAKa7BHuHGIsfZjWpOC5yjKFb7IF5KxOo5RcwfeB03GGCIFWQ8hAMZZ8e45hIMMkDa69qPvVWZiN4ASd+8Lg7tvQJAM6JBFZDBAVvqSvB+cLriVQkMpkWNVlDpxpdSO3ssOVr5YLikcSKe2iLzxKjjNWY0W7aau4LKMu91aoESj/9dqg=='),
    'JWT_PUBLIC_KEY'       => env('JWT_PUBLIC_KEY', 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLpzUE8tzxX6JNjh/JdAkwRLZGI4k43aPf9iQG4xUecRHRfra1nv0LSygN6JTzFIBDLavO02olfwKbK/dniNB1/8RN4B1IiU3Ws5XOgpIfOiSiUP7ztVcyzzdETbIg3uDA8UVEkUd4qZTbIO5cdqtVvoVvSJqIIKI5mFAGvA3drwIDAQAB'),

    // 允许的算法 多个逗号隔开
    'JWT_ALLOW_ALGORITHMS' => '', // 不填默认支持所有 经测试 HS256 HS512 HS384 一般 0.001s左右    RS256 RS384 RS512  一般 0.27左右
];