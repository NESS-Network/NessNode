<?php
namespace internals\lib;

class Output {
    public static function info(array $info) {
        $output = [
            'result' => 'info',
            'info' => $info
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
    }

    public static function data(array $data) {
        $output = [
            'result' => 'data',
            'data' => $data
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
    }

    public static function error(string $message) {
        $output = [
            'result' => 'error',
            'error' => $message
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
    }

    public static function message(string $message) {
        $output = [
            'result' => 'message',
            'message' => $message
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
    }

    public static function encrypted(string $data, $sig) {
        $output = [
            'result' => 'encrypted',
            'data' => $data,
            'sig' => $sig
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
    }

    public static function text(string $text) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $text;
    }

    public static function textJson(string $text) {
        header('Content-Type: application/json; charset=utf-8');
        echo $text;
    }
}