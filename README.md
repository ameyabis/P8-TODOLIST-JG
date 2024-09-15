ToDoList
========

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

========

Bienvenue sur la mise en place du projet.
- Clonez votre projet
- Installer Composer
- Configurer votre fichier .env
- Installer les dependences manquantes
- Créer la base de données
- Ajout des données

## Clonez votre projet
```sh
git clone https://github.com/ameyabis/P8-TODOLIST-JG
```

## Installez composer
Voici le [lien](https://getcomposer.org/doc/00-intro.md) pour installer Composer.

## Configurer votre fichier .env
Créer un fichier .env a partir du fichier .env.dist
Remplire la variable DATA_URL pour pouvoir se connecter à la base de donnéess MYSQL

## Installez les dependences manquantes
```sh
composer install
```

## Créez la base de données
```sh
php bin/console doctrine:database:create
```

## Ajoutez les champs dans la tables
```sh
php bin/console doctrine:migrations:migrate
```

## Ajoutez un jeu de données
```sh
php bin/console doctrine:fixtures:load
```

## Démarer le projet
```sh
symfony server:start
```
