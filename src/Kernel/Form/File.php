<?php

namespace EasyDouYin\Kernel\Form;

use const PATHINFO_EXTENSION;

use EasyDouYin\Kernel\Exceptions\RuntimeException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\Part\DataPart;

use function file_put_contents;
use function md5;
use function pathinfo;
use function strtolower;
use function sys_get_temp_dir;
use function tempnam;

class File extends DataPart
{
    /**
     * @throws \EasyDouYin\Kernel\Exceptions\RuntimeException
     */
    public static function from(
        string $pathOrContents,
        string $filename = null,
        string $contentType = null,
        string $encoding = null
    ): DataPart {
        if (file_exists($pathOrContents)) {
            return static::fromPath($pathOrContents, $filename, $contentType);
        }

        return static::fromContents($pathOrContents, $filename, $contentType, $encoding);
    }

    /**
     * @throws \EasyDouYin\Kernel\Exceptions\RuntimeException
     */
    public static function fromContents(
        string $contents,
        string $filename = null,
        string $contentType = null,
        string $encoding = null
    ): DataPart {
        if (null === $contentType) {
            $mimeTypes = new MimeTypes();

            if ($filename) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $contentType = $mimeTypes->getMimeTypes($ext)[0] ?? 'application/octet-stream';
            } else {
                $tmp = tempnam(sys_get_temp_dir(), 'easydouyin');
                if (! $tmp) {
                    throw new RuntimeException('Failed to create temporary file.');
                }

                file_put_contents($tmp, $contents);
                $contentType = $mimeTypes->guessMimeType($tmp) ?? 'application/octet-stream';
                $filename = md5($contents).'.'.($mimeTypes->getExtensions($contentType)[0] ?? null);
            }
        }

        return new self($contents, $filename, $contentType, $encoding);
    }
}
