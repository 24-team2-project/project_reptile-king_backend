<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */

    'projects' => [
        'app' => [

            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project and its related services using a
             * server SDK, requests must be authenticated. For server-to-server
             * communication this is done with a Service Account.
             *
             * If you don't already have generated a Service Account, you can do so by
             * following the instructions from the official documentation pages at
             *
             * https://firebase.google.com/docs/admin/setup#initialize_the_sdk
             *
             * Once you have downloaded the Service Account JSON file, you can use it
             * to configure the package.
             *
             * If you don't provide credentials, the Firebase Admin SDK will try to
             * auto-discover them
             *
             * - by checking the environment variable FIREBASE_CREDENTIALS
             * - by checking the environment variable GOOGLE_APPLICATION_CREDENTIALS
             * - by trying to find Google's well known file
             * - by checking if the application is running on GCE/GCP
             *
             * If no credentials file can be found, an exception will be thrown the
             * first time you try to access a component of the Firebase Admin SDK.
             *
             */

            // 'credentials' => env('FIREBASE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),
            'credentials' => [
                "type" => "service_account",
                "project_id" => "pachungking-7381e",
                "private_key_id" => "01ca5f240650f1ba3fe84a352de2c36f6f1cde77",
                "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC1VotsoPw6Y673\nbPg4GULSjlYXujxlUh+bSizmJwJvQsW+Dg+NeWJGr71A1kBpSbrVfpM4W/3VXqxX\n6pq7if8BORx5tFJOzHVwhZnlbam0q7/2Eu9MrmlVHkFeZ/JwGR2k3TP7SCsGYuCf\n87wCg3nlSgWbqh6il5stpzN1AmS9H9LnILpyG4gzO+KlBRsTtYR/Vi6o+K5+yb/f\nnPfwzakLPZtuEBOuWVH4w+Mo7qvJ3GEvL1IW6GLZZLLRr/dM6LY1oanOz5xuSfqX\ntBqnTvrXJfoZ90VA9XjEKtwqxmicLyqPNUtA+pXNQX+okXrwSRlfkj5lkf+HQ4uV\nCicWPKz/AgMBAAECggEALwGmtbuLdqVVey0DsSMXN9aOyarMsiUWLV6OggC1uFqy\nQMCLVoyTJaizoeWIH+LtbB9aEV+DTraybT3S629rq4j+8SuFtIh6nvb6/EavmbZ1\n1dd1scZ6Zoanwk2mwlk4Xn661VYRVR/3g28emvYy+HV2O7JfK52qK6zugVPkAkQX\n2bVhy5M8UVciBLt8DZQsq+Wpf/cLinbmaveO2Zg0UKZSUbT0YbtW17Bvxkf1hd4N\nRe7vSdJBvKKq+U7H98OOUg1+0qDNESjitY2hyf+CfNoBtgKNcaRcREu3Hjg4+EXe\n4Y9I4iohLtWgbse2g+CxCGSCXacCzlHKblAkl7ORzQKBgQDt9h7UfrDXYJ/aoOH6\n9gLvRCEfMoGGOkvZwLi9p679H1t4HV/BWn9SRw0bJpVwz8yNSRnXNF14H+vZiLUV\nj0PqwLlHG1rGcI6QPWLXEO08lQoB4FME8UVzUEZsnEzjdhN1gBh7LbscO/576d0T\n5BqcGE+y6G+ZoRd/nzxevnIgKwKBgQDDFZdrpr7BPT8DLGqpS7zPn2VpMHYvbNj2\ndwuuXhR6jrmBO39iNR1ueUDzHF6FxkS7oV21w2TxLPhQ4XWlGjZcNUBgG3X8WZQW\nWA1MLq4LnfwyFwYrnlu776eJH0KdANCX6qaLYkMb2o9yvLiOn/99jk1cTDjhs4BD\ngMTqeBTofQKBgQCHswv/x8OW/v4J0icsUzB3O5Xb0ZR1dDcfFT0EwmQS6tfhlnat\nr6rdw7Dgo88ixw1yIJbA15bZ0vJPWhtSESH89Cx0NVA9y6Aw9yCvXnK7Uo6jZUZS\nkjg1uzh95WAfco1EO2k6jWifgELubP3qwvC9xUtlzhEePfRFjgwCR36TgwKBgBZG\nUdrF2EQCNT6shKU985oVTiP7l5MEr6U8pIXNUjNINqAt5faVr/2cNLFNjPFjWRe/\nbg7B97Wd9+BsTd2DJ6/RmL8gg5FDvSfr9+C5979104T4ogi69Sh5TbzXZ+i7XhXq\nggeqOZVlmDl2mPAYDrkMVYLzZQ9ISp3qhxFqkqCVAoGBANmPj49A2yNSQ49p3rmI\nEwEaAAzDlHxQiZPr8nTgtlH+gzxTWCRR0mE9WLSpSNHBamunS0jix81VSZ720p5z\n7bghR3wNOpshBj3IMStlUMWrka6QH7T0gTcl4NwYxhNFobWPspNT9rIN6f11ROqd\nkrn7Y3yMng/aQ8FCa/z/Lpia\n-----END PRIVATE KEY-----\n",
                "client_email" => "firebase-adminsdk-d8rrr@pachungking-7381e.iam.gserviceaccount.com",
                "client_id" => "116761116837333325877",
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-d8rrr%40pachungking-7381e.iam.gserviceaccount.com",
                "universe_domain" => "googleapis.com",
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Auth Component
             * ------------------------------------------------------------------------
             */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firestore Component
             * ------------------------------------------------------------------------
             */

            'firestore' => [

                /*
                 * If you want to access a Firestore database other than the default database,
                 * enter its name here.
                 *
                 * By default, the Firestore client will connect to the `(default)` database.
                 *
                 * https://firebase.google.com/docs/firestore/manage-databases
                 */

                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [

                /*
                 * In most of the cases the project ID defined in the credentials file
                 * determines the URL of your project's Realtime Database. If the
                 * connection to the Realtime Database fails, you can override
                 * its URL with the value you see at
                 *
                 * https://console.firebase.google.com/u/1/project/_/database
                 *
                 * Please make sure that you use a full URL like, for example,
                 * https://my-project-id.firebaseio.com
                 */

                'url' => env('FIREBASE_DATABASE_URL'),

                /*
                 * As a best practice, a service should have access to only the resources it needs.
                 * To get more fine-grained control over the resources a Firebase app instance can access,
                 * use a unique identifier in your Security Rules to represent your service.
                 *
                 * https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
                 */

                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],

            ],

            'dynamic_links' => [

                /*
                 * Dynamic links can be built with any URL prefix registered on
                 *
                 * https://console.firebase.google.com/u/1/project/_/durablelinks/links/
                 *
                 * You can define one of those domains as the default for new Dynamic
                 * Links created within your project.
                 *
                 * The value must be a valid domain, for example,
                 * https://example.page.link
                 */

                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [

                /*
                 * Your project's default storage bucket usually uses the project ID
                 * as its name. If you have multiple storage buckets and want to
                 * use another one as the default for your application, you can
                 * override it here.
                 */

                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),

            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * The Firebase Admin SDK can cache some data returned from the Firebase
             * API, for example Google's public keys used to verify ID tokens.
             *
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Enable logging of HTTP interaction for insights and/or debugging.
             *
             * Log channels are defined in config/logging.php
             *
             * Successful HTTP messages are logged with the log level 'info'.
             * Failed HTTP messages are logged with the log level 'notice'.
             *
             * Note: Using the same channel for simple and debug logs will result in
             * two entries per request and response.
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Behavior of the HTTP Client performing the API requests
             */

            'http_client_options' => [

                /*
                 * Use a proxy that all API requests should be passed through.
                 * (default: none)
                 */

                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),

                /*
                 * Set the maximum amount of seconds (float) that can pass before
                 * a request is considered timed out
                 *
                 * The default time out can be reviewed at
                 * https://github.com/kreait/firebase-php/blob/6.x/src/Firebase/Http/HttpClientOptions.php
                 */

                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),

                'guzzle_middlewares' => [],
            ],
        ],
    ],
];
