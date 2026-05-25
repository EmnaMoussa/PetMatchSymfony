# PetMatch Symfony

Projet Symfony separe pour PetMatch.

## Demarrage local avec XAMPP

1. Demarrer Apache et MySQL dans XAMPP.
2. Creer la base MySQL :

```bash
C:\xampp\php\php.exe bin/console doctrine:database:create
```

3. Lancer les migrations :

```bash
C:\xampp\php\php.exe bin/console doctrine:migrations:migrate
```

4. Lancer le serveur Symfony avec PHP :

```bash
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

5. Ouvrir :

```text
http://127.0.0.1:8000
```

## Comptes de test

Email :

```text
sara@petmatch.tn
karim@petmatch.tn
nour@petmatch.tn
yasmine@petmatch.tn
```

Mot de passe :

```text
password
```

## Fonctionnalites

- Inscription avec animal
- Connexion/deconnexion
- Modification profil
- Swipe avec creation de match
- Liste des matches
- Chat entre proprietaires
