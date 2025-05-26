<?php

declare(strict_types=1);

namespace LapostaApi\Adapter;

class CurlAdapter
{
    public function init(): \CurlHandle|false
    {
        return curl_init();
    }

    public function setopt(\CurlHandle $handle, int $option, mixed $value): bool
    {
        return curl_setopt($handle, $option, $value);
    }

    public function exec(\CurlHandle $handle): string|false
    {
        return curl_exec($handle);
    }

    public function getInfo(\CurlHandle $handle, int $option = 0): mixed
    {
        return curl_getinfo($handle, $option);
    }

    public function getErrno(\CurlHandle $handle): int
    {
        return curl_errno($handle);
    }

    public function getError(\CurlHandle $handle): string
    {
        return curl_error($handle);
    }

    public function close(\CurlHandle $handle): void
    {
        curl_close($handle);
    }
}
