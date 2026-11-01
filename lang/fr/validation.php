<?php

// Seulement les règles utilisées par Bolduc ; en ajouter une ici quand un formulaire s'en sert.
return [
    'required' => 'Ce champ est obligatoire.',
    'email' => 'Cet e-mail n\'a pas l\'air valide.',
    'unique' => 'Cette valeur est déjà utilisée.',
    'confirmed' => 'Les deux saisies ne correspondent pas.',
    'string' => 'Ce champ doit être un texte.',
    'min' => [
        'string' => ':min caractères au moins.',
        'numeric' => 'Au moins :min.',
    ],
    'max' => [
        'string' => ':max caractères au plus.',
        'numeric' => ':max au plus.',
    ],
];
