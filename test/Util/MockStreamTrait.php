<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Util;

use Laminas\Diactoros\Stream;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\StreamInterface;

trait MockStreamTrait
{
    /**
     * @param array<string|int,mixed> $data
     * @return StreamInterface
     */
    private function createStream(array $data): StreamInterface
    {
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            Assert::fail('Cannot open php://memory for creating stream');
        }
        fwrite(
            $stream,
            (string)json_encode($data)
        );
        rewind($stream);

        return new Stream($stream);
    }
}