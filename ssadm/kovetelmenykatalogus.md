TODO: tartalmát bemásolni a `.docx`-be.

# Követelménykatalógus 

## Funkcionális követelmények

### Tárolandó adatok

#### Szak

- Megnevezés: rövid szöveg
- Típus: bsc/msc
- Előrelátott tartam: hány félév
- Tanterv

A tanterv meghatározza, hogy egy adott szakon mely kurzusok kötelezőek, kötválok vagy szabválok, illetve, hogy melyik félévben ajánlott.


#### Felhasználó

- Kód: 6 karakteres kód, betűket vagy számokat tartalmaz, kis-/nagybetű nem számít
- Név
- Jelszó
- E-mail
- Születési idő

Egy felhasználó lehet hallgató is. Ebben a szerepben a következő plusz adatokat kapja meg:

- Szak
- Kezdés éve

Hallgatóként felvehet kurzusokat és amennyiben az előadás, az azokhoz tartozó vizsgákat. A felvett kurzusoknak eltároljuk az érdemjegyét is.

Egy felhasználó lehet oktató is, ha van olyan kurzus ahol oktatóként van megjelölve. Ilyenkor létrehozhat vizsgákat, jegyeket írhat be az adott kurzust felvett hallgatóknak.

Egy felhasználó lehet admin is, ekkor hozhat létre tárgyakat, kurzusokat és rendelhet hozzájuk oktatókat, szakokat, tantervet és szerkesztheti azokat. Ő vehet fel további felhasználókat is, valamint módosíthatja az adataikat.

#### Tárgy

- Kód
- Név
- Jóváhagyásos-e
- Mennyi kreditet ér
- Gyakorlat vagy előadás
- Előfeltétel
  - Milyen tárgy

Egy tárgy több kurzust foglal össze. Egy tárgyhoz tartozó kurzust csak akkor lehet felvenni, ha az előfeltételként megadott tárgy már teljesítve van.

#### Kurzus

- Sorszám -> Ebből jön ki a kódja `[Tárgy kód]-[Kurzus sorszám]`
- Melyik tárgyhoz tartozik
- Terem
- Időpont
- Férőhely
- Melyik félévhez tartozik

#### Terem

- Kód
- Férőhely

#### Vizsga

- Melyik tárgyhoz tartozik
- Melyik teremben lesz
- Időpont
- Vizsgáztató(k)
- Férőhely

## Nem funkcionális követelmények

- A jelszavak biztonságosan vannak eltárolva
- Input mezők kezelése biztonságos
