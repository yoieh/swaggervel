<?php namespace Appointer\Swaggervel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controller;

class SwaggervelController extends Controller
{
    public function definitions($page = 'api-docs.json')
    {
        if (config('swaggervel.auto-generate')) {
            $this->regenerateDefinitions();
        }

        $filePath = config('swaggervel.doc-dir') . "/{$page}";

        if (File::extension($filePath) === "") {
            $filePath .= '.json';
        }

        if (!File::exists($filePath)) {
            app()->abort(404, "Cannot find {$filePath}");
        }

        $content = File::get($filePath);

        return response($content, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function ui(Request $request)
    {
        if (config('swaggervel.auto-generate')) {
            $this->regenerateDefinitions();
        }

        if (config('swaggervel.behind-reverse-proxy')) {
            $proxy = $request->server('REMOTE_ADDR');
            $request->setTrustedProxies(array($proxy));
        }

        //need the / at the end to avoid CORS errors on Homestead systems.
        $response = response()->view('swaggervel::index', [
                'urlToDocs' => url(config('swaggervel.doc-route')),
                'requestHeaders' => config('swaggervel.requestHeaders'),
                'clientId' => $request->input('client_id'),
                'clientSecret' => $request->input('client_secret'),
                'realm' => $request->input('realm'),
                'appName' => $request->input('appName'),
                'apiKey' => config('swaggervel.api-key'),
            ]
        );

        foreach (config('swaggervel.viewHeaders', []) as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }

    private function regenerateDefinitions()
    {
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
}
