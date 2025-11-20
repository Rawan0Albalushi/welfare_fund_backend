<?php

namespace App\Helpers;

class PaymentSecurityHelper
{
    /**
     * التحقق من صحة return_origin باستخدام قائمة بيضاء
     * 
     * @param string|null $returnOrigin
     * @return string|null
     * @throws \Exception
     */
    public static function validateReturnOrigin(?string $returnOrigin): ?string
    {
        if (empty($returnOrigin)) {
            return null;
        }

        // الحصول على القائمة البيضاء من config أو env
        $allowedOrigins = self::getAllowedOrigins();
        
        // تحليل URL للتحقق من النطاق
        $parsedUrl = parse_url($returnOrigin);
        
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            throw new \Exception('Invalid return_origin URL format');
        }

        $host = $parsedUrl['host'];
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $port = $parsedUrl['port'] ?? null;

        // التحقق من أن البروتوكول آمن في الإنتاج (HTTPS)
        if (app()->environment('production') && $scheme !== 'https') {
            throw new \Exception('return_origin must use HTTPS in production');
        }

        // بناء النطاق الكامل مع البورت إن وجد
        $fullHost = $host . ($port ? ':' . $port : '');

        // التحقق من وجود النطاق في القائمة البيضاء
        $isAllowed = false;
        foreach ($allowedOrigins as $allowed) {
            $allowedParsed = parse_url($allowed);
            if (!$allowedParsed) {
                continue;
            }
            
            $allowedHost = $allowedParsed['host'];
            $allowedPort = $allowedParsed['port'] ?? null;
            $allowedFullHost = $allowedHost . ($allowedPort ? ':' . $allowedPort : '');

            // مطابقة دقيقة للنطاق
            if ($fullHost === $allowedFullHost) {
                $isAllowed = true;
                break;
            }

            // دعم wildcard subdomains (*.example.com)
            if (strpos($allowedHost, '*') === 0) {
                $pattern = '/^' . str_replace(['*', '.'], ['.*', '\.'], substr($allowedHost, 2)) . '$/i';
                if (preg_match($pattern, $host)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed) {
            throw new \Exception('return_origin is not in the allowed origins whitelist');
        }

        // إرجاع URL نظيف
        return rtrim($returnOrigin, '/');
    }

    /**
     * الحصول على قائمة النطاقات المسموحة
     * 
     * @return array
     */
    private static function getAllowedOrigins(): array
    {
        // الحصول من env variable (مفصولة بفواصل)
        $envOrigins = env('ALLOWED_RETURN_ORIGINS', '');
        $origins = [];

        if (!empty($envOrigins)) {
            $origins = array_map('trim', explode(',', $envOrigins));
            $origins = array_filter($origins);
        }

        // إضافة FRONTEND_ORIGIN كبديل
        $frontendOrigin = env('FRONTEND_ORIGIN');
        if ($frontendOrigin && !in_array($frontendOrigin, $origins)) {
            $origins[] = $frontendOrigin;
        }

        // في بيئة التطوير، السماح بـ localhost
        if (app()->environment(['local', 'development'])) {
            $origins[] = 'http://localhost:3000';
            $origins[] = 'http://localhost:49887';
            $origins[] = 'http://127.0.0.1:3000';
            $origins[] = 'http://127.0.0.1:49887';
        }

        return array_unique($origins);
    }

    /**
     * تنظيف URL وإزالة البيانات الحساسة من السجلات
     * 
     * @param string $url
     * @return string
     */
    public static function sanitizeUrlForLogging(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed) {
            return '[INVALID_URL]';
        }

        // إخفاء query parameters الحساسة
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            // إخفاء البيانات الحساسة
            $sensitiveKeys = ['token', 'session_id', 'secret', 'key', 'password'];
            foreach ($sensitiveKeys as $key) {
                if (isset($query[$key])) {
                    $query[$key] = '[REDACTED]';
                }
            }
            $parsed['query'] = http_build_query($query);
        }

        // إعادة بناء URL
        $url = ($parsed['scheme'] ?? 'http') . '://';
        if (isset($parsed['user'])) {
            $url .= $parsed['user'];
            if (isset($parsed['pass'])) {
                $url .= ':***';
            }
            $url .= '@';
        }
        $url .= $parsed['host'] ?? '';
        if (isset($parsed['port'])) {
            $url .= ':' . $parsed['port'];
        }
        $url .= $parsed['path'] ?? '';
        if (isset($parsed['query'])) {
            $url .= '?' . $parsed['query'];
        }
        if (isset($parsed['fragment'])) {
            $url .= '#' . $parsed['fragment'];
        }

        return $url;
    }
}

