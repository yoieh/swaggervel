<?php
use Illuminate\Http\Request;

Route::any(config('swaggervel.doc-route') . '/{page?}', function ($page = 'api-docs.json') {
    $filePath = config('swaggervel.doc-dir') . "/{$page}";

    if (File::extension($filePath) === "") {
        $filePath .= ".json";
    }

    if (!File::exists($filePath)) {
        app()->abort(404, "Cannot find {$filePath}");
    }

    $content = File::get($filePath);
    return response($content, 200, array(
        'Content-Type' => 'application/json'
    ));
});

Route::get(config('swaggervel.api-docs-route'), function (Request $request) {
    if (config('swaggervel.generateAlways')) {
        $dir = config('swaggervel.app-dir');
        if (is_array($dir)) {
            $appDir = [];
            foreach($dir as $d) {
                $appDir[] = base_path($d);
            }
        } else {
            $appDir = base_path($dir);
        }

        $docDir = config('swaggervel.doc-dir');

        if (!File::exists($docDir)) {
            File::makeDirectory($docDir);
        }

        if (is_writable($docDir)) {
            $excludeDirs = config('swaggervel.excludes');

            $swagger = \Swagger\scan($appDir, [
                'exclude' => $excludeDirs
            ]);

            $filename = $docDir . '/api-docs.json';
            file_put_contents($filename, $swagger);
        }
    }

    if (config('swaggervel.behind-reverse-proxy')) {
        $proxy = $request->server('REMOTE_ADDR');
        $request->setTrustedProxies(array($proxy));
    }

    //need the / at the end to avoid CORS errors on Homestead systems.
    $response = response()->view('swaggervel::index', [
            'secure' => $request->secure(),
            'urlToDocs' => url(config('swaggervel.doc-route')),
            'requestHeaders' => config('swaggervel.requestHeaders'),
            'clientId' => $request->input("client_id"),
            'clientSecret' => $request->input("client_secret"),
            'realm' => $request->input("realm"),
            'appName' => $request->input("appName"),
            'apiKey' => config("swaggervel.api-key"),
        ]
    );

    foreach (config('swaggervel.viewHeaders', []) as $key => $value) {
        $response->header($key, $value);
    }

    return $response;
});
