<?php

namespace App;

class FileUpload
{
    private string $dir;
    private string $file;
    private string $fileName;
    private string $imageFileType;
    private array $allowedFileTypes;
    private string $tmpFile;
    private string $fileSize;
    private int $maxFileSize;
    /**
     * True when tmpFile is a filesystem path (a real $_FILES upload), false when it
     * holds raw file bytes directly (set via setTmpFile(), e.g. decoded data-URI
     * uploads) - readTmpFileBytes() needs to know which it's dealing with.
     */
    private bool $tmpFileIsPath = false;
    public function __construct(string $dir, string $fileName, array $allowedFileTypes, int $maxFileSize, array $FILES){
        $this->dir = $dir;
        $this->fileName = $fileName;
        $this->allowedFileTypes = $allowedFileTypes;
        $this->maxFileSize = $maxFileSize;
        $this->file = $this->dir . $this->fileName;
        $this->imageFileType = strtolower(pathinfo($this->file,PATHINFO_EXTENSION));
        if(!empty($FILES)){
            $this->tmpFile = $FILES["fileUpload"]["tmp_name"];
            $this->fileSize = $FILES["fileUpload"]["size"];
            $this->tmpFileIsPath = true;
        }
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $this->dir . $file;
    }

    /**
     * @return string
     */
    public function getImageFileType(): string
    {
        return $this->imageFileType;
    }

    /**
     * @param string $imageFileType
     */
    public function setImageFileType(string $imageFileType): void
    {
        $this->imageFileType = $imageFileType;
    }

    /**
     * @return array
     */
    public function getAllowedFileTypes(): array
    {
        return $this->allowedFileTypes;
    }

    /**
     * @param array $allowedFileTypes
     */
    public function setAllowedFileTypes(array $allowedFileTypes): void
    {
        $this->allowedFileTypes = $allowedFileTypes;
    }

    /**
     * @return string
     */
    public function getTmpFile(): string
    {
        return $this->tmpFile;
    }

    /**
     * @param string $tmpFile
     */
    public function setTmpFile(string $tmpFile): void
    {
        $this->tmpFile = $tmpFile;
    }

    /**
     * @return string
     */
    public function getFileSize(): string
    {
        return $this->fileSize;
    }

    /**
     * @param string $fileSize
     */
    public function setFileSize(string $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return int
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize
     */
    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }


    public function checkIfFileExists(): bool
    {
        return file_exists($this->file);
    }
    public function checkIfCorrectFileType(): bool
    {
        return in_array($this->imageFileType, $this->allowedFileTypes);
    }

    /**
     * Verifies the actual uploaded bytes match the declared extension instead of
     * trusting the destination filename/client-supplied MIME type - checkIfCorrectFileType()
     * alone only checks the extension we're about to write to, which an attacker fully
     * controls. tmpFile holds either a real filesystem path ($_FILES uploads, e.g. the
     * category icon form) or raw decoded bytes (base64 data-URI uploads), so both are
     * read the same way here.
     */
    public function checkContentMatchesDeclaredType(): bool
    {
        $bytes = $this->readTmpFileBytes();
        if($bytes === false || $bytes === ''){
            return false;
        }
        if($this->imageFileType === 'svg'){
            return self::isSafeSvg($bytes);
        }
        $info = @getimagesizefromstring($bytes);
        if($info === false){
            return false;
        }
        $detectedExtension = match ($info['mime']) {
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };
        return $detectedExtension !== null && $detectedExtension === $this->imageFileType;
    }

    private function readTmpFileBytes(): string|false
    {
        if(!isset($this->tmpFile) || $this->tmpFile === ''){
            return false;
        }
        if($this->tmpFileIsPath){
            return is_file($this->tmpFile) ? file_get_contents($this->tmpFile) : false;
        }
        return $this->tmpFile;
    }

    private static function isSafeSvg(string $svg): bool
    {
        if(str_contains($svg, '<?php') || str_contains($svg, '<%') || !str_contains($svg, '<svg')){
            return false;
        }
        if(stripos($svg, '<!doctype') !== false || stripos($svg, '<!entity') !== false){
            return false;
        }
        $previousSetting = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $loaded = $doc->loadXML($svg, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);
        if(!$loaded || $doc->documentElement === null || strtolower($doc->documentElement->localName) !== 'svg'){
            return false;
        }
        return self::svgSubtreeIsSafe($doc->documentElement);
    }

    private static function svgSubtreeIsSafe(\DOMElement $node): bool
    {
        $deniedTags = ['script', 'foreignobject', 'iframe', 'object', 'embed', 'link'];
        if(in_array(strtolower($node->localName), $deniedTags, true)){
            return false;
        }
        foreach (iterator_to_array($node->attributes) as $attr) {
            $name = strtolower($attr->name);
            if(str_starts_with($name, 'on')){
                return false;
            }
            if(in_array($name, ['href', 'xlink:href'], true)){
                $value = strtolower(trim(preg_replace('/[\x00-\x1F\s]+/', '', $attr->value)));
                if($value !== '' && !str_starts_with($value, '#')){
                    return false;
                }
            }
            if($name === 'style' && preg_match('/expression\s*\(|javascript:|vbscript:/i', $attr->value)){
                return false;
            }
        }
        if(strtolower($node->localName) === 'style' && preg_match('/expression\s*\(|javascript:|vbscript:|@import/i', $node->textContent)){
            return false;
        }
        foreach ($node->childNodes as $child) {
            if($child instanceof \DOMElement && !self::svgSubtreeIsSafe($child)){
                return false;
            }
        }
        return true;
    }
    public function checkFileSize(): bool
    {
        return $this->fileSize < $this->maxFileSize;
    }
    public function upload(): void
    {
        move_uploaded_file($this->tmpFile, $this->file);
    }
}