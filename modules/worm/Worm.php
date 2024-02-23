<?php
namespace modules\worm;

class Worm {

    public static function isUser(string $xml): bool 
    {
        $xml = preg_replace("/<!--.+?-->/i", '', $xml);
        $xml = preg_replace('/”/i', '"', $xml);
        $xmlObject = simplexml_load_string($xml);

        if (false === $xmlObject || NULL === $xmlObject->user) {
            return false;
        }

        return true;
    }

    public static function parseUser(string $xml): array 
    {
        $xml = preg_replace("/<!--.+?-->/i", '', $xml);
        $xml = preg_replace('/”/i', '"', $xml);
        $xmlObject = simplexml_load_string($xml);

        $type = (string) $xmlObject->user['type'];
        $nonce = (string) $xmlObject->user['nonce'];
        $tags = (string) $xmlObject->user['tags'];
        $public_key = (string) $xmlObject->user['public'];
        $verify_key = (string) $xmlObject->user['verify'];

        $tags = explode(',', $tags);

        return [
            'type' => $type,
            'nonce' => $nonce,
            'tags' => $tags,
            'public' => $public_key,
            'verify' => $verify_key
        ];
    }

    public static function isNode(string $xml): bool 
    {
        $xml = preg_replace("/<!--.+?-->/i", '', $xml);
        $xml = preg_replace('/”/i', '"', $xml);
        $xmlObject = simplexml_load_string($xml);

        if (false === $xmlObject || NULL === $xmlObject->node) {
            return false;
        }

        if ('ness' !== (string) $xmlObject->node['type']) {
            return false;
        }

        if (empty( $xmlObject->node['url'])) {
            return false;
        }

        if (empty( $xmlObject->node['nonce'])) {
            return false;
        }

        if (empty( $xmlObject->node['public'])) {
            return false;
        }

        if (empty( $xmlObject->node['verify'])) {
            return false;
        }

        return true;
    }

    public static function parseNode(string $xml): array 
    {
        $xml = preg_replace("/<!--.+?-->/i", '', $xml);
        $xml = preg_replace('/”/i', '"', $xml);
        $xmlObject = simplexml_load_string($xml);

        $type = (string) $xmlObject->node['type'];
        $url = (string) $xmlObject->node['url'];
        $nonce = (string) $xmlObject->node['nonce'];
        $tags = (string) $xmlObject->node['tags'];
        $public = (string) $xmlObject->node['public'];
        $verify = (string) $xmlObject->node['verify'];
        $master = (string) $xmlObject->node['master-user'];
        $tariff = (float) $xmlObject->node['tariff'];

        $tags = explode(',', $tags);

        return [
            'type' => $type,
            'url' => $url,
            'nonce' => $nonce,
            'tags' => $tags,
            'public' => $public,
            'verify' => $verify,
            'master' => $master,
            'tariff' => $tariff
        ];
    }
}
