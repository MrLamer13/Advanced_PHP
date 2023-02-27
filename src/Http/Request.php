<?php

namespace GeekBrains\LevelTwo\Http;

use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\JsonException;

class Request
{
    public function __construct(
        private array  $get,
        private array  $server,
        private string $body
    )
    {
    }

    public function method(): string
    {
        // В суперглобальном массиве $_SERVER HTTP-метод хранится под ключом REQUEST_METHOD
        if (!array_key_exists('REQUEST_METHOD', $this->server)) {
            // Если мы не можем получить метод - бросаем исключение
            throw new HttpException('Не удается получить метод из запроса');
        }

        return $this->server['REQUEST_METHOD'];
    }

    public function path(): string
    {
        if (!array_key_exists('REQUEST_URI', $this->server)) {
            throw new HttpException('Не удается получить путь из запроса');
        }

        $components = parse_url($this->server['REQUEST_URI']);

        if (!is_array($components) || !array_key_exists('path', $components)) {
            throw new HttpException('Не удается получить путь из запроса');
        }

        return $components['path'];

    }

    public function query(string $param): string
    {
        if (!array_key_exists($param, $this->get)) {
            throw new HttpException("Нет такого параметра запроса в запросе: $param");
        }

        $value = trim($this->get[$param]);

        if (empty($value)) {
            throw new HttpException("Пустой параметр запроса в запросе: $param");
        }

        return $value;

    }

    public function header(string $header): string
    {
        $headerName = mb_strtoupper("http_" . str_replace('-', '_', $header));

        if (!array_key_exists($headerName, $this->server)) {
            throw new HttpException("Нет такого заголовка в запросе: $header");
        }

        $value = trim($this->server[$headerName]);

        if (empty($value)) {
            throw new HttpException("Пустой заголовок в запросе: $header");
        }

        return $value;

    }

    // Метод для получения массива, сформированного из json-форматированного тела запроса
    public function jsonBody(): array
    {
        try {
            // Пытаемся декодировать json
            $data = json_decode(
                $this->body,
                // Декодируем в ассоциативный массив
                associative: true,
                // Бросаем исключение при ошибке
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new HttpException("Невозможно декодировать тело json");
        }

        if (!is_array($data)) {
            throw new HttpException("Не массив/объект в теле json");
        }

        return $data;
    }

    // Метод для получения отдельного поля из json-форматированного тела запроса
    public function jsonBodyField(string $field): mixed
    {
        $data = $this->jsonBody();

        if (!array_key_exists($field, $data)) {
            throw new HttpException("Нет такого поля: $field");
        }

        if (empty($data[$field])) {
            throw new HttpException("Пустое поле: $field");
        }

        return $data[$field];
    }

}