<?php

namespace iutnc\touiteur\model;

class User {
    private int $id;
    private string $pseudo;
    private string $nom;
    private string $prenom;
    private string $email;
    private int $nbTouites;
    private string $dateInscription;

    public function __construct(
        int $id,
        string $pseudo,
        string $nom,
        string $prenom,
        string $email,
        int $nbTouites = 0,
        string $dateInscription = ''
    ) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->nbTouites = $nbTouites;
        $this->dateInscription = $dateInscription ?: date('Y-m-d H:i:s');
    }

    public function getId(): int {
        return $this->id;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getNbTouites(): int {
        return $this->nbTouites;
    }

    public function getDateInscription(): string {
        return $this->dateInscription;
    }

    public function getNomComplet(): string {
        return $this->prenom . ' ' . $this->nom;
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }
}
