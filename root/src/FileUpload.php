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


    public function checkIfImage(): bool
    {
        if(getimagesize($this->tmpFile) !== false) {
            return true;
        }
        else{
            return false;
        }
    }
    public function checkIfFileExists(): bool
    {
        return file_exists($this->file);
    }
    public function checkIfCorrectFileType(): bool
    {
        return in_array($this->imageFileType, $this->allowedFileTypes);
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