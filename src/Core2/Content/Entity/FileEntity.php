<?php

namespace Drupal\drift_eleven\Core2\Content\Entity;

use Drupal\drift_eleven\Core2\Content\Entity\Base\EntityBase;
use InvalidArgumentException;

class FileEntity extends EntityBase {
  protected int $id;
  protected ?string $alt;
  protected ?string $title;
  protected ?int $width;
  protected ?int $height;
  protected ?string $description;
  protected string $filename;
  protected string $uri;
  protected string $url;
  protected string $filemime;
  protected int $filesize;
  protected string $status;
  protected int $created;
  protected int $changed;

  public function __construct(array $values = []) {
    $this->unpackValues($values);

    if (!isset($this->id, $this->filename, $this->uri, $this->url, $this->filemime, $this->filesize, $this->status, $this->created, $this->changed)) {
      throw new InvalidArgumentException('Missing required properties for FileEntity');
    }
  }

  /**
   * @return array<string, array{
   *  id: int,
   *  alt: ?string,
   *  title: ?string,
   *  width: ?int,
   *  height: ?int,
   *  description: ?string,
   *  filename: string,
   *  uri: string,
   *  url: string,
   *  filemime: string,
   *  filesize: int,
   *  status: string<'temporary'|'permanent'>,
   *  created: int,
   *  changed: int
   * }>
   */
  public function toArray(): array {
    return [
      'id' => $this->getId(),
      'alt' => $this->getAlt(),
      'title' => $this->getTitle(),
      'width' => $this->getWidth(),
      'height' => $this->getHeight(),
      'description' => $this->getDescription(),
      'filename' => $this->getFilename(),
      'uri' => $this->getUri(),
      'url' => $this->getUrl(),
      'filemime' => $this->getFilemime(),
      'filesize' => $this->getFilesize(),
      'status' => $this->getStatus(),
      'created' => $this->getCreated(),
      'changed' => $this->getChanged(),
    ];
  }

  public function getId(): int {
    return $this->id;
  }
  public function getAlt(): ?string {
    return $this->alt;
  }
  public function getTitle(): ?string {
    return $this->title;
  }
  public function getWidth(): ?int {
    return $this->width;
  }
  public function getHeight(): ?int {
    return $this->height;
  }
  public function getDescription(): ?string {
    return $this->description;
  }
  public function getFilename(): string {
    return $this->filename;
  }
  public function getUri(): string {
    return $this->uri;
  }
  public function getUrl(): string {
    return $this->url;
  }
  public function getFilemime(): string {
    return $this->filemime;
  }
  public function getFilesize(): int {
    return $this->filesize;
  }
  public function getStatus(): string {
    return $this->status;
  }
  public function getCreated(): int {
    return $this->created;
  }
  public function getChanged(): int {
    return $this->changed;
  }

  public function setId(int $id): self {
    $this->id = $id;
    return $this;
  }
  public function setAlt(?string $alt): self {
    $this->alt = $alt;
    return $this;
  }
  public function setTitle(?string $title): self {
    $this->title = $title;
    return $this;
  }
  public function setWidth(?int $width): self {
    $this->width = $width;
    return $this;
  }
  public function setHeight(?int $height): self {
    $this->height = $height;
    return $this;
  }
  public function setDescription(?string $description): self {
    $this->description = $description;
    return $this;
  }
  public function setFilename(string $filename): self {
    $this->filename = $filename;
    return $this;
  }
  public function setUri(string $uri): self {
    $this->uri = $uri;
    return $this;
  }
  public function setUrl(string $url): self {
    $this->url = $url;
    return $this;
  }
  public function setFilemime(string $filemime): self {
    $this->filemime = $filemime;
    return $this;
  }
  public function setFilesize(int $filesize): self {
    $this->filesize = $filesize;
    return $this;
  }
  public function setStatus(string $status): self {
    $this->status = $status;
    return $this;
  }
  public function setCreated(int $created): self {
    $this->created = $created;
    return $this;
  }
  public function setChanged(int $changed): self {
    $this->changed = $changed;
    return $this;
  }
}
