TODO: tartalmát bemásolni a `.docx`-be.

# Követelménykatalógus 

## Funkcionális követelmények

### Tárolandó adatok

#### Szak

- Megnevezés: rövid szöveg
- Típus: bsc/msc
- Előrelátott tartam: hány félév

#### Felhasználó

- Kód: 6 karakteres kód, betűket vagy számokat tartalmaz, kis-/nagybetű nem számít
- Név
- Jelszó
- E-mail
- Születési idő

Egy felhasználó lehet hallgató is. Ebben a szerepben a következő plusz adatokat kapja meg:

- Szak
- Kezdés éve

Hallgatóként felvehet kurzusokat és az azokhoz tartozó vizsgákat.

Egy felhasználó lehet oktató is, ha van olyan kurzus ahol oktatóként van megjelölve. Ilyenkor létrehozhat vizsgákat, jegyeket írhat be az adott kurzust felvett hallgatóknak.

Egy felhasználó lehet admin is, ekkor hozhat létre tárgyakat, kurzusokat és rendelhet hozzájuk oktatókat. Ő vehet fel további felhasználókat is.

#### Tárgy
#### Kurzus
#### Terem
#### Vizsga
#### Időszak

## Nem funkcionális követelmények
