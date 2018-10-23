# Databáze konferencí a publikací

Aplikácia je dostupná na adrese <https://ddd.fit.cvut.cz/PubConf/>, prihlásime sa do nej cez SSO. Základom aplikácie je PHP framework _Nette_, dokumentáciu k nemu nájdeme na <https://doc.nette.org>.

## Prístup na server

Na produkčný server pristupujeme cez SSH. Teda klasicky spustíme príkaz `ssh PubConf@ddd.fit.cvut.cz` a po výzve zadáme heslo. Môžeme využiť aj SSH kľúče a nahrať svoj verejný kľúč na server aby sme nemuseli zakaždým písať heslo.

Okrem SSH môžeme použiť kopírovanie súborov pomocou `scp` alebo FTP klienta, prístupové údaje sú rovnaké. Toto však odporúčam len pre stiahnutie databázy, na deploy aplikácie je lepšie použiť _git_.

Aplikácia je v zložke `/usr/local/PubConf`, _DocumentRoot_ je potom v zložke `www`. To je bezpečnostné opatrenie kvôli zamedzeniu prístupu ku konfiguračným súborom a tiež jednotlivým PHP súborom.

V zložke `log` je súbor `error.log`, do neho sa ukladajú warningy a errory. V prípade chyby sa užívateľom zobrazí obyčajná chybová stránka a do logu sa zapíše príčina. Ďalej je tu súbor `access.log`, v ňom nájdeme chyby 404.

## Nastavenie vývojového prostredia u seba

Pre vývoj na svojom počítači potrebujeme webový server a databázu. Webový server odporúčam _Apache_, databázu _MySQL_, takto to beží aj na serveri. V zásade máme dve možnosti, prvá je nainštalovať si kompletný balík so serverom, databázou a nejakými ďalšími funkciami alebo si nainštalovať osobitne webový server a osobitne databázu. Výhodou komplet balíka je jednoduchá inštalácia a konfigurovanie všetkých nástrojov na jednom mieste. Najznámejšie takéto balíky sú _MAMP_ a _XAMPP_.

Stránky odkiaľ stiahnúť server, databázu alebo komplet:
* MAMP: <http://mamp.info/>
* XAMPP: <https://www.apachefriends.org/>
* Apache: <https://httpd.apache.org/>
* MySQL: <https://www.mysql.com>

### Nastavenie

V konfigurácii webového servera nastavíme _DocumentRoot_ na zložku `www` z naklonovaného _git_ repozitára. Na databázovom serveri vytvoríme novú databázu a údaje o databáze zapíšeme do súboru `app/config/config.local.neon` v _git_ repozitári aby sa aplikácia vedela do databázy pripojiť. Uistime sa že do zložiek `log` a `temp` môžeme zapisovať. 

### Otestovanie funkčnosti

Po úspešnom nastavení a zapnutí webového servera sa nám zobrazí úvodná stránka aplikácie s formulárom na prihlásenie. Otestujeme pripojenie k databáze zadaním ľubovoľného reťazce do políčka _Login_ aj _Password_, ak sa aplikácia nevie pripojiť k databáze ukáže sa nám stránka s nápisom `Connection refused`, inak iba chyba nad formulárom že sme zadali zlé údaje.

### Zriadenie prístupu do aplikácie

Ak server aj databáza fungujú pustíme sa do posledného kroku a tým je nastavenie prístupu do aplikácie. Ide o to mať v tabuľke `auth_login_password` uložený login a heslo, tá však obsahuje FK z tabuľky `submitter`. Musíme teda pridať záznam do tabuľky `submitter` a potom do tabuľky `auth_login_password` s existujúcim `submitter_id`. Ak máme už importovanú databázu zo servera môžeme pridávanie záznamov preskočiť a jednoducho zmeniť heslo jednému z už existujúcich používateľov. Je dobré vybrať (ak vkladáme vlastného užívateľa tak spraviť nový záznam s rolou `admin`) si užívateľa, ktorý má rolu `admin`, pretože ostatné zmeny potom vieme robiť iba pomocou samotnej aplikácie. Rolu užívateľa nájdeme v tabuľke `user_role`, kde `user_id` je FK z tabuľky `submitter`. Keď už máme vybratého alebo vloženého používateľa potrebujeme mu nastaviť heslo, avšak keďže v databáze sa ukladajú heslá v zahashovanej podobe potrebujeme zistiť hash nášho hesla a do databázy vložiť ten. Na to si pomôžeme trikom, do súboru `app/bootstrap.php` pridáme riadok hneď za príkazy `use` a `require`: `echo \Nette\Security\Passwords::hash('heslo');`, kde _heslo_ je samozrejme naše želané heslo. Tento príkaz vypíše výstup z _Nette_ hashovacej funkcie, ale keď načítame stránku aby sme videl výstup zobrazí sa nám chyba `Cannot modify header information`. Toto obídeme kliknutím na šípku smerujúcu dole v pravom hornom rohu, chyba sa skryje a na stránke uvidíme zahashované heslo, ktoré skopírujeme do databázy a môžeme sa prihlásiť s loginom a heslom. Pridaný riadok potom zo súboru samozrejme odstránime.

## Databáza

Prístup do databázy na serveri je cez _Adminer_ zakázaný, ten je dostupný iba z lokálneho počítača. Ak potrebujeme musíme použiť príkaz `mysql` na serveri cez SSH. Odporúčam takto iba prezerať dáta, meniť niečo iba v najnutnejšom prípade.

### Export

Export databázy zo servera do súboru spravíme pomocou `mysqldump`, konkrétný príkaz vyzerá takto: `mysqldump -u PubConf -p PubConf > databaseexport.sql`. Príkaz si ešte vypýta heslo, jedná sa o heslo k databáze nie SSH.

### Import

Import databázy môžeme spraviť cez CLI, v tom prípade použijeme príkaz `mysql -u user -p db < databaseexport.sql`, kde _user_ je užívateľské meno pre lokálnu databázu a *db* názov databázy. Druhá možnosť je použiť grafický nástroj, napr. _phpMyAdmin_, ten býva zvyčajne pribalený keď inštalujeme databázu spolu s webovým serverom a ďalšími nástrojmi. Spolu s Nette je pribalený _Adminer_, ten je dostupný na adrese http://localhost/adminer ak webový server beží na adrese _localhost_.  Do phpMyAdmina alebo Adminera stačí v sekcii Import nahrať súbor ktorý sme exportovali a on sa postará o import.

## Deploy na server

Na serveri je v zložke s aplikáciou inicializovaný _git_ repozitár napojený na školský _GitLab_ a v ňom projekt PubConf. Repozitár je na branchi _master_, neodporúčam to meniť. Deploy po novom commite do mastera teda spravíme jednoduchým príkazom `git pull`. Podobne pomocou _gitu_ môžeme vrátiť zmeny ak by bolo treba.

## Nástroje

### Tracy

_Tracy_ je súčasťou _Nette_ a slúži na zjednodušenie debugovania aplikácie. Je automaticky dostupná na lokálnej inštalácii, na serveri je zakázaná. _Tracy_ sme už videli keď sme získavali hash hesla, ukáže nám časť súboru kde nastala chyba, aj približne aká chyba nastala. Okrem errorov aj warningov je však prítomná na každej stránke v pravom dolnom rohu, kde ukazuje informácie ako čas načítania stránky, dotazy smerujúce do databázy, identitu užívateľa, ktorý presenter s akými parametrami sa volal a ďalšie. Do _Tracy_ si môžeme nechať vypísať premenné, stačí z hociktorého miesta v aplikácii zavolať funkciu `bdump()`, ktorej dáme premennú ako parameter a pri načítaní stránky sa zobrazí v _Tracy_.
