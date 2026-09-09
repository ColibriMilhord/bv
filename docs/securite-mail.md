# Suspension de la boîte d'envoi — causes et remèdes

Hostinger a suspendu l'envoi depuis `reservation@bellevuedaveyron.fr` pour
« activité suspecte continue » et « compromission potentielle ». Ce document
explique ce qui a rendu cet incident possible, ce qui a été corrigé dans le
code, et ce qui reste à faire de votre côté — car le correctif logiciel ne
suffit pas.

---

## Ce qui s'est passé, en une phrase

Le mot de passe de la boîte d'envoi a circulé en clair dans un dépôt GitHub
**public**, et le formulaire de réservation permettait par ailleurs d'envoyer
des messages en série vers des adresses choisies par le visiteur. Deux portes
ouvertes, chacune suffisante pour expliquer la suspension.

## Les trois causes, par ordre de gravité

### 1. Le mot de passe SMTP était lisible publiquement

`config/mail_config.php` contenait la constante `SMTP_PASS` en clair, et le
dépôt `ColibriMilhord/bv` est public : n'importe qui pouvait la lire. Des
robots parcourent GitHub en continu à la recherche exactement de cela ; le
délai entre la publication d'un identifiant et sa première utilisation
frauduleuse se compte en minutes.

Un tiers disposant de ce mot de passe s'authentifie sur `smtp.hostinger.com`
comme s'il était vous, et envoie ce qu'il veut. C'est l'explication la plus
probable de la mention « compromission potentielle » : les messages
incriminés ne sont sans doute jamais passés par le site.

**Corrigé côté code** : les identifiants ont été sortis des fichiers versionnés
et vivent désormais dans `config/secrets.php`, présent sur le serveur
uniquement. **Non corrigé côté compte** : le mot de passe reste valide tant que
vous ne l'avez pas changé, et il reste lisible dans l'historique Git.

### 2. Le formulaire servait de relais

Chaque soumission déclenchait deux envois depuis la boîte du gîte : un aux
propriétaires, et **un accusé de réception vers l'adresse saisie par le
visiteur**. Ce second envoi est le point sensible : le destinataire est choisi
par celui qui remplit le formulaire.

Sans limite de fréquence, sans piège à robots et sans délai minimal, un script
pouvait soumettre le formulaire en boucle et faire partir des milliers de
messages depuis `reservation@` vers des adresses arbitraires. Pour le
fournisseur, cela ressemble exactement à un envoi de spam — parce que c'en est
un.

### 3. Les en-têtes n'étaient pas nettoyés

Le nom saisi par le visiteur alimentait directement le sujet du message, et
l'adresse saisie alimentait `To:`, `Reply-To:` et la commande SMTP `RCPT TO:`
sans validation. Une valeur contenant un retour à la ligne — la technique
classique dite d'*injection d'en-tête* — permettait d'ajouter des
destinataires cachés à un message parti de votre boîte :

```
Nom : Marie[retour à la ligne]Bcc: victime1@ailleurs.fr, victime2@ailleurs.fr
```

---

## Ce qui a été corrigé dans le code

### `config/mail_smtp.php` — l'envoi lui-même

- chaque destinataire est validé par `filter_var(..., FILTER_VALIDATE_EMAIL)`
  avant toute connexion ; une adresse contenant un retour à la ligne est
  rejetée, l'envoi n'a pas lieu ;
- le sujet est débarrassé de ses retours à la ligne ;
- une adresse de réponse invalide est ignorée plutôt que recopiée ;
- `RCPT TO:` est émis destinataire par destinataire, à partir de la liste
  validée, et non plus à partir de la chaîne brute.

### `config/antispam.php` — quatre barrages, aucun captcha

| Barrage | Principe | Coût pour le visiteur |
|---|---|---|
| Champ piège | Un champ invisible que seuls les robots remplissent | nul |
| Délai minimal | Un formulaire envoyé en moins de 3 secondes n'a pas été rempli à la main | nul |
| Limite par réseau | 3 demandes par heure, 8 par jour et par préfixe réseau | nul en usage normal |
| Contrôle du contenu | Refus d'un lien dans le nom, de plus de deux liens dans le message, d'une adresse invalide | nul |

Un captcha aurait été le réflexe évident ; il a été écarté volontairement. Il
dégrade l'expérience sur un site haut de gamme, écarte une partie des
visiteurs réels, et les services de résolution automatisée le contournent pour
quelques centimes. Les quatre mesures ci-dessus arrêtent l'essentiel du trafic
automatisé sans que le visiteur ne s'aperçoive de rien.

Chaque tentative, acceptée ou refusée, est consignée dans la table
`envois_formulaire` (date, préfixe réseau, motif du refus). Le bilan est
visible dans **Administration → Audience du site**.

### `index.php` — l'enchaînement

- le contrôle a lieu **avant** l'écriture en base et **avant** tout envoi : une
  soumission refusée ne consomme rien ;
- le nom et le téléphone sont nettoyés de leurs retours à la ligne et bornés en
  longueur avant d'alimenter le sujet ou le corps du message ;
- l'adresse du visiteur est validée, et non simplement « nettoyée » — la
  fonction `FILTER_SANITIZE_EMAIL` utilisée jusqu'ici laissait passer
  `a@b.fr\r\nBcc:` ;
- **l'accusé de réception n'est envoyé que si le message aux propriétaires est
  bien parti** : le seul envoi détournable est désormais conditionné à la
  réussite d'un envoi qui, lui, ne l'est pas ;
- le fichier `bellevue_debug_mail.log` n'est plus écrit. Il se trouvait à la
  racine du site, contenait les adresses e-mail de vos clients, et se
  consultait par `index.php?show_log=1`. Les envois en échec sont désormais
  consignés dans le journal d'erreurs PHP, hors du dossier public.

---

## Faut-il changer l'adresse d'envoi ?

**Non.** Vous aviez posé la règle vous-même : l'expéditeur reste
`reservation@bellevuedaveyron.fr`, et c'est la bonne décision.

L'adresse d'envoi n'est pour rien dans l'incident. Basculer sur une autre boîte
reviendrait à déplacer le problème : la nouvelle adresse serait suspendue à son
tour dans les mêmes conditions, et elle partirait sans réputation d'envoi —
donc avec un risque accru d'arriver en indésirables. Pire, l'adresse de contact
`accueil@bellevuedaveyron.com` sert à votre correspondance : la faire servir
aussi d'expéditeur automatique exposerait votre boîte principale.

La séparation actuelle est la bonne : `reservation@` émet, `accueil@` et
`milhord@gmail.com` reçoivent. Le `Reply-To:` porte l'adresse du client, si
bien qu'un simple « Répondre » écrit au bon endroit.

---

## Ce qu'il vous reste à faire — dans cet ordre

1. **Changer le mot de passe de la boîte `reservation@`** dans hPanel
   (Emails → Comptes e-mail → Changer le mot de passe), puis reporter la
   nouvelle valeur dans `config/secrets.php` sur le serveur. Tant que ce n'est
   pas fait, le reste ne sert à rien : l'ancien mot de passe circule.
2. **Changer aussi le mot de passe de la base de données**, exposé de la même
   façon, et le reporter dans `config/secrets.php`.
3. **Publier cette version** (voir `docs/DEPLOIEMENT.md`), pour que les
   protections soient effectivement en place.
4. **Supprimer `bellevue_debug_mail.log`** du serveur s'il s'y trouve encore :
   il contient les adresses de vos clients et se télécharge librement.
5. **Répondre à Hostinger** en demandant la réactivation, en indiquant que le
   mot de passe a été changé et que le formulaire a été sécurisé. Ce message
   accélère la levée de la suspension ; il suffit d'être factuel.
6. **Rendre le dépôt GitHub privé** (Settings → General → Danger Zone →
   Change repository visibility). À défaut, tout ce qui y est publié reste
   lisible par les robots de collecte.
7. **Vérifier les messages envoyés** depuis la boîte `reservation@` sur les
   dernières semaines : la présence de messages que vous n'avez pas écrits
   confirmerait l'accès frauduleux, leur absence orienterait vers l'abus du
   formulaire.

> **Sur l'historique Git.** Rendre le dépôt privé ne réécrit pas son passé :
> les anciens mots de passe restent lisibles dans les commits antérieurs pour
> qui a accès au dépôt. C'est sans conséquence une fois les mots de passe
> changés — d'où l'ordre des étapes ci-dessus. Une réécriture d'historique est
> possible si vous la souhaitez, mais elle n'est pas nécessaire.

---

## Pour la suite — si les envois indésirables reprenaient

Les seuils sont regroupés en tête de `config/antispam.php` et se resserrent
d'une ligne :

```php
const ANTISPAM_DELAI_MIN    = 3;   // secondes
const ANTISPAM_MAX_PAR_H    = 3;   // demandes par heure et par réseau
const ANTISPAM_MAX_PAR_JOUR = 8;   // demandes par jour et par réseau
const ANTISPAM_LIENS_MAX    = 2;   // liens tolérés dans le message
```

Consultez d'abord **Administration → Audience du site** : les motifs de refus y
sont détaillés. Si « champ piège rempli » domine, les barrages font leur
travail. Si les refus sont rares alors que les envois indésirables persistent,
c'est que la boîte est utilisée directement, sans passer par le site — et la
réponse est alors un nouveau changement de mot de passe.

---

## Transposable à artifacile.fr

Trois règles, valables pour tout formulaire qui déclenche un envoi :

1. **Aucun identifiant dans le code versionné**, même sur un dépôt supposé
   privé. Un fichier de secrets hors dépôt, lu au démarrage, coûte dix lignes.
2. **Tout envoi vers une adresse fournie par l'utilisateur est un relais
   potentiel.** Le limiter en fréquence, et le conditionner à la réussite d'un
   envoi non détournable.
3. **Valider, et non nettoyer.** `FILTER_VALIDATE_EMAIL` refuse ;
   `FILTER_SANITIZE_EMAIL` transforme et laisse passer. Sur une valeur qui
   alimente un en-tête, la seule réponse acceptable est le refus.
