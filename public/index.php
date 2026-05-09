<?php

if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool {
        $expectedKey = 0;
        foreach ($array as $key => $value) {
            if ($key !== $expectedKey) {
                return false;
            }
            $expectedKey++;
        }
        return true;
    }
}


use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
