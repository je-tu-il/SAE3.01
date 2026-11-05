<?php

namespace iutnc\touiteur\model;

class Image {
    private int $id;
    private string $chemin;
    private ?string $description;

    public function __construct(int $id, string $chemin, ?string $description = null) {
        $this->id = $id;
        $this->chemin = $chemin;
        $this->description = $description;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getChemin(): string {
        return $this->chemin;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }
}
