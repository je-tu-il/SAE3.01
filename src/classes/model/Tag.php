<?php

namespace iutnc\touiteur\model;

class Tag {
    private int $id;
    private string $libelle;
    private ?string $description;
    private int $nbMentions;

    public function __construct(int $id, string $libelle, ?string $description = null, int $nbMentions = 0) {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
        $this->nbMentions = $nbMentions;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getLibelle(): string {
        return $this->libelle;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getNbMentions(): int {
        return $this->nbMentions;
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }
}
