<?php
/*
* TWIG CI ALTITUDE DEV
*/
use Twig\Environment;
use Twig\Extra\Cache\CacheExtension;
use Twig\Extra\CssInliner\CssInlinerExtension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twig\Extra\Cache\CacheRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use App\ThirdParty\TwigExtensions\JsonDecodeExtension;

if (! function_exists('twig')) {
    function twig($cache = true, $optimized = true, $pathOverride = false){

        $loader = new \Twig\Loader\FilesystemLoader($pathOverride ?: APPPATH . 'Views');
        $twig   = new \Twig\Environment($loader, [
            'cache'             => $cache ? WRITEPATH.'/cache/twig' : false,
            'auto_reload'       => true,
            'charset'           => 'utf-8',
            'optimizations'     => $optimized ? -1 : 0,
            'strict_variables'  => false,
            'debug'             => ENVIRONMENT === 'development'
        ]);

        if (ENVIRONMENT === 'development') {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        $twig->addExtension(new CssInlinerExtension());
        $twig->addExtension(new CacheExtension());
        $twig->addExtension(new JsonDecodeExtension());

        $twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load($class) {
                if (CacheRuntime::class === $class) {
                    return new CacheRuntime(new TagAwareAdapter(new FilesystemAdapter('altitude_cached', 0, WRITEPATH . '/cache/twig')));
                }
            }
        });

        /*
        * Global Defines CI > Twig
        * ex your define('PHPV', phpversion());
        * use $twig->addGlobal('phpversion', phpversion());
         *is now available in twig template with : {{ phpversion }}
        */
        $twig->addGlobal('TTLCONTENT', 3600);

        /*
        * Functions CI > Twig
        */
        $twig->addFunction(new \Twig\TwigFunction('lang', 'lang'));
        $twig->addFunction(new \Twig\TwigFunction('session', function($key = null) {
            $session = session();
            return $key ? $session->get($key) : $session;
        }));
        $twig->addFunction(new \Twig\TwigFunction('url_to', 'url_to'));
        $twig->addFunction(new \Twig\TwigFunction('route_to', 'route_to'));
        $twig->addFunction(new \Twig\TwigFunction('csrf_hash', 'csrf_hash'));
        $twig->addFunction(new \Twig\TwigFunction('csrf_token', 'csrf_token'));
        $twig->addFunction(new \Twig\TwigFunction('site_url', 'site_url'));
        $twig->addFunction(new \Twig\TwigFunction('base_url', 'base_url'));
        $twig->addFunction(new \Twig\TwigFunction('current_url', 'current_url'));

        return $twig;
    }

}