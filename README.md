# MURICA 

## Installáció

*Megjegyzés:* feltételezem, hogy az xampp konfigurálva van `oci8` használatához.

1. Másold a `murica_*` mappákat a `xampp/htdocs` mappába.
2. A `murica_api/configs.json.template` fájlnév végéről töröld a `.template` kiterjesztést.
3. A fájlban írd át a `user` és `password` mezőket a megfelelő értékekre.
4. Ha szükséges írd át a `connection_string`-et.
5. A `table_owner` mező azt írja le, hogy kinek az accountján szereplő táblákat használja (kabinetes jelszó megosztása nélkül lehessen ugyan azokat az adatokat kezelni).
6. Ha szükséges írd át a `host_name` mezőt arra, amin a xampp elérhető (például konfigurációtól függ, hogy `http`-t, vagy `https`-t kell használni).

Kabinet külső elérését a következő ssh csatlakozással lehet elérni:

```shell
ssh -L 1521:orania2.inf.u-szeged.hu:1521 [h-s azonosító]@linux.inf.u-szeged.hu
```