# Suspension de la boîte d'envoi — causes et remèdes

Hostinger a suspendu l'envoi depuis `reservation@bellevuedaveyron.fr` pour
« activité suspecte continue » et « compromission potentielle ». Ce document
explique ce qui a rendu cet incident possible, ce qui a été corrigé dans le
code, et ce qui reste à faire de votre côté — car le correctif logiciel ne
suffit pas.

---

## Ce qui s'est passé, en une phrase

Le formulaire de réservation permettait d'envoyer des messages en série, depuis
la boîte du gîte, vers des adresses choisies par celui qui remplissait le
formulaire. C'est la seule anomalie établie, et elle suffit à expliquer la
suspension.

> **Une hypothèse écartée.** J'avais d'abord conclu que le dépôt GitHub était
> public et que le mot de passe SMTP y avait été moissonné. **C'est faux : le
> dépôt est privé.** Ma vérification était mal faite — la requête passait par
> un proxy authentifié, qui renvoie « accès autorisé » pour un dépôt privé
> comme pour un dépôt public. Le champ `visibility` du dépôt vaut bien
> `private`. Le mot de passe reste à changer, pour les raisons données plus
> bas, mais ce n'est plus le premier suspect.

## Les causes, par ordre de vraisemblance

### 1. Le formulaire servait de relais

Chaque soumission déclenchait deux envois depuis la boîte du gîte : un aux
propriétaires, et **un accusé de réception vers l'adresse saisie par le
visiteur**. Ce second envoi est le point sensible : le destinataire est choisi
par celui qui remplit le formulaire.

Sans limite de fréquence, sans piège à robots et sans délai minimal, un script
pouvait soumettre le formulaire en boucle et faire partir des milliers de
messages depuis `reservation@` vers des adresses arbitraires. Pour le
fournisseur, cela ressemble exactement à un envoi de spam — parce que c'en est
un.

### 2. Les en-têtes n'étaient pas nettoyés

Le nom saisi par le visiteur alimentait directement le sujet du message, et
l'adresse saisie alimentait `To:`, `Reply-To:` et la commande SMTP `RCPT TO:`
sans validation. Une valeur contenant un retour à la ligne — la technique
classique dite d'*injection d'en-tête* — permettait d'ajouter des
destinataires cachés à un message parti de votre boîte :

```
Nom : Marie[retour à la ligne]Bcc: victime1@ailleurs.fr, victime2@ailleurs.fr
```

Un seul envoi légitime en apparence pouvait ainsi arroser une liste entière.
Combiné au point précédent, c'est un dispositif d'envoi de masse complet.

### 3. Le mot de passe SMTP, à changer par précaution et non par certitude

`config/mail_config.php` contenait la constante `SMTP_PASS` en clair. Le dépôt
est privé, donc il n'a pas été moissonné par les robots qui parcourent GitHub.
Il reste cependant lisible dans l'historique des commits, présent dans toute
copie locale du dépôt, et il l'était en clair dans un fichier du serveur.

Cela ne prouve rien, mais un mot de passe de boîte e-mail se change de toute
façon après un incident de ce type : la mention « compromission potentielle »
d'Hostinger désigne peut-être un accès direct à la boîte, obtenu par une tout
autre voie — un poste sur lequel le compte est configuré, une réutilisation du
mot de passe ailleurs, une tentative par dictionnaire. Le changer coûte cinq
minutes et referme toutes ces hypothèses d'un coup.

**Corrigé côté code** : les identifiants sont sortis des fichiers versionnés et
vivent désormais dans `config/secrets.php`, présent sur le serveur uniquement.
**Non corrigé côté compte** : le mot de passe reste valide tant que vous ne
l'avez pas changé.

---

## Comment trancher entre les deux scénarios

Les deux causes possibles — abus du formulaire, ou accès direct à la boîte —
se distinguent facilement, et la réponse oriente la suite :

| À regarder | Si c'est le formulaire | Si c'est un accès direct |
|---|---|---|
| Dossier **Éléments envoyés** de `reservation@` | Uniquement des accusés de réception à votre format | Des messages que vous ne reconnaissez pas |
| **Adresse IP d'envoi**, à demander à Hostinger avec les exemples de messages | Celle de votre hébergement | Une adresse étrangère au serveur |

Demandez ces éléments dans votre réponse à Hostinger : ils les ont, et ils
répondent à la question en une ligne. Dans les deux cas, les corrections
ci-dessous et le changement de mot de passe restent à faire.

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

## L'envoi lui-même était-il correct ? Non.

Fermer le formulaire ne suffisait pas : la couche d'envoi elle-même comportait
des défauts qui pèsent sur le sort d'un message à l'arrivée. Ils ont été repris.

### Ce qui pouvait casser un envoi, ou pire

| Défaut | Conséquence |
|---|---|
| Le corps n'échappait pas le point en début de ligne | Une ligne réduite à un point met fin aux données SMTP. Le message du visiteur en contenant une était tronqué, **et la suite interprétée comme des commandes du protocole** — une injection, cette fois au niveau SMTP et non des en-têtes. |
| Aucun en-tête `Date` ni `Message-ID` | Deux absences que les filtres anti-spam tiennent pour un signe de message fabriqué à la main. Elles cassent aussi le fil de discussion chez le destinataire. |
| Les réponses du serveur n'étaient pas vérifiées | Un destinataire refusé passait inaperçu : le site annonçait « envoyé » alors que le message n'était allé nulle part. |
| Le corps partait en 8 bits sans encodage déclaré | Une ligne de plus de 998 caractères — un message un peu long sans retour à la ligne — pouvait être coupée n'importe où par un relais. |
| Le salut `EHLO` reprenait l'en-tête `Host` de la requête | Une valeur fournie par le visiteur se retrouvait dans le dialogue SMTP. |

### Ce qui a été mis en place

- **Échappement du point** sur l'ensemble des données transmises, conformément
  au protocole. Vérifié : un message contenant une ligne réduite à un point
  arrive intact, point compris.
- **`Date` et `Message-ID`** émis à chaque envoi, l'identifiant construit sur le
  domaine d'expédition.
- **Chaque réponse du serveur contrôlée** — authentification, expéditeur,
  destinataires, ouverture des données, acceptation finale. Un destinataire
  refusé est consigné dans le journal, et un envoi sans aucun destinataire
  accepté est rapporté comme un échec, non comme un succès.
- **Encodage « quoted-printable »** des deux corps : le texte reste lisible tel
  quel dans la source du message, les accents sont préservés et aucune ligne ne
  dépasse la limite du protocole.
- **`EHLO` sur le domaine d'expédition**, jamais sur une valeur venue de la
  requête.

Le tout a été vérifié face à un vrai serveur SMTP, en relisant octet par octet
ce qui part sur le fil.

### Ce qui reste à faire chez l'hébergeur — et qui compte autant

Le code ne peut rien pour l'authentification du domaine. Trois enregistrements
DNS décident, chez Gmail et Outlook, si un message est distribué, classé
indésirable ou rejeté. Ils se règlent dans hPanel → Domaines → **Zone DNS**, et
Hostinger propose un assistant pour les deux premiers :

| Enregistrement | Rôle | Sans lui |
|---|---|---|
| **SPF** | Déclare quels serveurs ont le droit d'envoyer au nom du domaine | N'importe qui peut se faire passer pour vous — et vos propres messages sont suspects |
| **DKIM** | Signe chaque message ; le destinataire vérifie qu'il n'a pas été modifié | Aucune preuve d'origine ; Gmail classe volontiers en indésirables |
| **DMARC** | Dit quoi faire d'un message qui échoue aux deux premiers, et vous fait remonter des rapports | Aucune visibilité sur les usurpations de votre domaine |

Après une suspension pour envoi indésirable, ces trois réglages sont le
meilleur investissement possible : ils rétablissent la réputation du domaine
bien plus vite que le temps.

Une fois en place, un envoi de test vers <https://www.mail-tester.com> donne une
note sur 10 et la liste de ce qui manque encore. Viser 9 ou 10.

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

1. **Publier cette version** (voir `docs/DEPLOIEMENT.md`). C'est ce qui ferme
   le relais : sans cela, la boîte sera suspendue à nouveau après sa
   réactivation.
2. **Changer le mot de passe de la boîte `reservation@`** dans hPanel
   (Emails → Comptes e-mail → Changer le mot de passe), puis reporter la
   nouvelle valeur dans `config/secrets.php` sur le serveur. Par précaution, et
   parce que cela referme d'un coup toutes les hypothèses d'accès direct.
3. **Changer aussi le mot de passe de la base de données**, présent dans le
   code de la même façon, et le reporter dans `config/secrets.php`.
4. **Supprimer `bellevue_debug_mail.log`** du serveur s'il s'y trouve encore :
   il est à la racine du site, contient les adresses de vos clients, et se
   télécharge librement. Supprimer également `check_db.php` et
   `fetch_datatourisme.php` s'ils y sont restés.
5. **Répondre à Hostinger** en demandant la réactivation : indiquez que le
   formulaire de contact envoyait un accusé de réception sans limitation, que
   ce point est corrigé (limitation de fréquence, piège à robots, validation
   des destinataires), et que le mot de passe a été changé. Demandez-leur au
   passage **des exemples de messages incriminés et l'adresse IP d'envoi** :
   c'est ce qui confirmera la cause.
6. **Regarder le dossier « Éléments envoyés »** de `reservation@` sur les
   dernières semaines, selon le tableau ci-dessus.

> **Sur l'historique Git.** Le dépôt est privé, mais les anciens mots de passe
> restent lisibles dans les commits antérieurs pour qui y a accès. C'est sans
> conséquence une fois les mots de passe changés — d'où l'étape 2. Une
> réécriture d'historique est possible si vous la souhaitez ; elle n'est pas
> nécessaire.

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

1. **Aucun identifiant dans le code versionné**, y compris sur un dépôt privé :
   l'historique se conserve indéfiniment, la visibilité d'un dépôt se change
   d'un clic, et une copie locale se retrouve sur n'importe quel poste. Un
   fichier de secrets hors dépôt, lu au démarrage, coûte dix lignes.
2. **Tout envoi vers une adresse fournie par l'utilisateur est un relais
   potentiel.** Le limiter en fréquence, et le conditionner à la réussite d'un
   envoi non détournable.
3. **Valider, et non nettoyer.** `FILTER_VALIDATE_EMAIL` refuse ;
   `FILTER_SANITIZE_EMAIL` transforme et laisse passer. Sur une valeur qui
   alimente un en-tête, la seule réponse acceptable est le refus.
