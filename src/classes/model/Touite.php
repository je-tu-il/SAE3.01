<?php

namespace iutnc\touiteur\model;

class Touite {
    private int $id;
    private string $texte;
    private string $datePublication;
    private int $note;
    private int $idAuteur;
    private string $auteurPseudo;
    private ?Image $image;
    private array $tags; // liste des libellés de tags

    public function __construct(
        int $id,
        string $texte,
        string $datePublication,
        int $note,
        int $idAuteur,
        string $auteurPseudo,
        ?Image $image = null,
        array $tags = []
    ) {
        $this->id = $id;
        $this->texte = $texte;
        $this->datePublication = $datePublication;
        $this->note = $note;
        $this->idAuteur = $idAuteur;
        $this->auteurPseudo = $auteurPseudo;
        $this->image = $image;
        $this->tags = $tags;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTexte(): string {
        return $this->texte;
    }

    public function getDatePublication(): string {
        return $this->datePublication;
    }

    public function getNote(): int {
        return $this->note;
    }

    public function getIdAuteur(): int {
        return $this->idAuteur;
    }

    public function getAuteurPseudo(): string {
        return $this->auteurPseudo;
    }

    public function getImage(): ?Image {
        return $this->image;
    }

    public function getTags(): array {
        return $this->tags;
    }

    public function setTags(array $tags): void {
        $this->tags = $tags;
    }

    public function addTag(string $tag): void {
        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }
}
