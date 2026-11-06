<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QRMarkupSVG;

class QRCodeService
{
    /**
     * Build the full public customer tracking URL for a given order token.
     */
    public static function getTrackingUrl(string $token): string
    {
        $baseUrl = rtrim((string)Config::get('app.url', 'http://localhost:8000'), '/');

        $configuredHost = parse_url($baseUrl, PHP_URL_HOST);
        $requestHost = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
        $requestHostname = preg_match('/^(?:localhost|\d{1,3}(?:\.\d{1,3}){3})(?::\d{1,5})?$/D', $requestHost)
            ? parse_url('http://' . $requestHost, PHP_URL_HOST)
            : null;
        $requestHostname = is_string($requestHostname) ? $requestHostname : null;

        if (in_array($configuredHost, ['localhost', '127.0.0.1', '::1'], true)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $port = null;

            if (self::isPrivateIpv4($requestHostname)) {
                $lanAddress = $requestHostname;
                $port = parse_url('http://' . $requestHost, PHP_URL_PORT);
            } elseif (in_array($requestHostname, ['localhost', '127.0.0.1', '::1'], true)) {
                $lanAddress = self::discoverLanIpv4();
                $port = parse_url($baseUrl, PHP_URL_PORT);
                $port ??= parse_url('http://' . $requestHost, PHP_URL_PORT);
            } else {
                $lanAddress = null;
            }

            if ($lanAddress !== null) {
                $baseUrl = "{$scheme}://{$lanAddress}" . ($port !== null ? ":{$port}" : '');
            } elseif (self::isPrivateIpv4($requestHostname)) {
                $baseUrl = "{$scheme}://{$requestHost}";
            }
        }

        return "{$baseUrl}/track/{$token}";
    }

    private static function discoverLanIpv4(): ?string
    {
        $addresses = gethostbynamel(gethostname()) ?: [];
        $addresses = array_values(array_filter($addresses, [self::class, 'isPrivateIpv4']));

        usort($addresses, static function (string $left, string $right): int {
            return self::lanAddressPriority($left) <=> self::lanAddressPriority($right);
        });

        return $addresses[0] ?? null;
    }

    private static function lanAddressPriority(string $address): int
    {
        if (str_starts_with($address, '192.168.')) {
            return 0;
        }

        if (str_starts_with($address, '10.')) {
            return 1;
        }

        return 2;
    }

    private static function isPrivateIpv4(?string $host): bool
    {
        if (!$host || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $ip = ip2long($host);
        return ($ip & 0xFF000000) === 0x0A000000
            || ($ip & 0xFFF00000) === 0xAC100000
            || ($ip & 0xFFFF0000) === 0xC0A80000;
    }

    /**
     * Generate an inline Data URI SVG QR Code string for embedding in HTML or printing.
     */
    public static function generateSvgDataUri(string $url): string
    {
        $options = new QROptions([
            'version'             => Version::AUTO,
            'outputType'          => QRMarkupSVG::class,
            'eccLevel'            => EccLevel::M,
            'imageBase64'         => true,
            'addQuietzone'        => true,
            'quietzoneSize'       => 2,
            'drawLightModules'    => false,
            'svgUseFileTag'       => false,
        ]);

        $qrcode = new QRCode($options);
        return $qrcode->render($url);
    }

    /**
     * Generate raw SVG markup for direct inline rendering.
     */
    public static function generateSvgMarkup(string $url): string
    {
        $options = new QROptions([
            'version'             => Version::AUTO,
            'outputType'          => QRMarkupSVG::class,
            'eccLevel'            => EccLevel::M,
            'imageBase64'         => false,
            'addQuietzone'        => true,
            'quietzoneSize'       => 2,
            'drawLightModules'    => false,
        ]);

        $qrcode = new QRCode($options);
        return $qrcode->render($url);
    }
}
