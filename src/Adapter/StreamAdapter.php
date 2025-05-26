<?php

declare(strict_types=1);

namespace LapostaApi\Adapter;

class StreamAdapter
{
    public function fopen(string $filename, string $mode)
    {
        return fopen($filename, $mode);
    }

    public function isResource(mixed $value): bool
    {
        return is_resource($value);
    }

    public function fread($stream, int $length): string|false
    {
        return fread($stream, $length);
    }

    public function fwrite($stream, string $string): int|false
    {
        return fwrite($stream, $string);
    }

    public function fstat($stream): array|false
    {
        return fstat($stream);
    }

    public function ftell($stream): int|false
    {
        return ftell($stream);
    }

    public function feof($stream): bool
    {
        return feof($stream);
    }

    public function fseek($stream, int $offset, int $whence = SEEK_SET): int
    {
        return fseek($stream, $offset, $whence);
    }

    public function rewind($stream): bool
    {
        return rewind($stream);
    }

    public function fclose($stream): bool
    {
        return fclose($stream);
    }

    public function streamGetContents($stream): string|false
    {
        return stream_get_contents($stream);
    }

    public function streamGetMetaData($stream): array
    {
        return stream_get_meta_data($stream);
    }
}
