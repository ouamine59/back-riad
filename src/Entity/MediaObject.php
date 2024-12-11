<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Repository\MediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: MediaObjectRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['media_object:read']],
    denormalizationContext:['groups' => ['media_object:write']],
    types: ['https://schema.org/MediaObject'],
    outputFormats: ['jsonld' => ['application/ld+json']],
    operations: [
        new Post(
            uriTemplate: '/api/upload',
            name:'app_upload_image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'file' => [
                                        'type'   => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ]
)]
#[Vich\Uploadable]
class MediaObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath')]
    #[Groups(['media_object:read', 'media_object:write'])]
    #[Assert\Regex('/^[a-zA-Z0-9_\-\. ]+\.[a-zA-Z0-9]{1,255}+$/')]
    #[Assert\NotBlank()]
    private ?File $file = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media_object:read', 'media_object:write'])]
    #[Assert\Regex('/^[a-zA-Z0-9_\-\. ]+\.[a-zA-Z0-9]{1,255}+$/')]
    #[Assert\NotBlank()]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['media_object:read', 'media_object:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Products::class, inversedBy: 'mediaObject')]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: true)]
    #[Groups(['media_object:read', 'media_object:write'])]
    private ?Products $products = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
        if ($file) {
            // Mettre à jour la date de mise à jour lorsque le fichier est modifié
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): static
    {
        $this->products = $products;

        return $this;
    }
}
