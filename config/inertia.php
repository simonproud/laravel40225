<?php

return [

    // Другие настройки Inertia...

    'ssr' => [
        'enabled' => true,

        /*
         | Путь к вашему собранному SSR-файлу
         | Убедитесь, что этот путь совпадает с результатом билда vite build --ssr
         | Например, если vite собрал файл в public/build/ssr/ssr.js:
         */
        'bundle' => public_path('build/ssr/ssr.js'),

        /*
         | Дополнительные настройки, если нужно
         */
        'debug' => false,
    ],
];
