<?php namespace App\Service;

use Vich\UploaderBundle\Handler\UploadHandler as BaseUploadHandler;

class UploadHandlerWrapper
{
    private $uploadHandler;

    public function __construct(BaseUploadHandler $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }

    public function upload($object, string $fieldName): void
    {
        $this->uploadHandler->upload($object, $fieldName);
    }
}