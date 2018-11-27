<?php
declare(strict_types=1);

namespace Rarst\ReleaseBelt;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Release instance, based on a file data.
 */
class Release
{
    protected const SEPARATORS = '.-_';

    // From https://github.com/composer/semver/blob/master/src/VersionParser.php
    public const MODIFIER_REGEX = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)((?:[.-]?\d+)*+)?)?([.-]?dev)?';

    public const VERSION_REGEX = '/(?P<package>.*?)(?P<version>v?(?:\d+\.*){1,4}'.self::MODIFIER_REGEX.')\.zip/';

    /** @var SplFileInfo $file */
    protected $file;

    public $path;
    public $filename;
    public $type;
    public $vendor;
    public $package;
    public $version;

    /**
     * Release constructor.
     */
    public function __construct(SplFileInfo $file)
    {
        $this->file     = $file;
        $this->path     = str_replace('\\', '/', $file->getRelativePath());
        $this->filename = $file->getFilename();
        $this->package  = $file->getBasename('.zip');
        $this->version  = 'dev-unknown';
        [$this->type, $this->vendor] = explode('/', $this->path);

        $matches = $this->parseFilename($this->filename);

        if (! empty($matches)) {
            $this->package = $matches['package'];
            $this->version = $matches['version'];
        }
    }

    /**
     * Parses package and version information out of a file name.
     */
    protected function parseFilename(string $filename): array
    {
        $matched = preg_match(self::VERSION_REGEX, $filename, $matches);

        if (empty($matched)) {
            return [];
        }

        return [
            'package' => trim(rtrim($matches['package'], self::SEPARATORS)),
            'version' => trim(ltrim($matches['version'], 'v')),
        ];
    }
}
