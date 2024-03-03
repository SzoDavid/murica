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

Egy felhasználó lehet admin is, ekkor hozhat létre tárgyakat, kurzusokat és rendelhet hozzájuk oktatókat, szakokat, teljesítési követelményeket és tantervet szerkeszthet. Ő vehet fel további felhasználókat is.

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

#### Időszak

Például kurzus felvételi időszak, szorgalmi időszak, vizsga időszak, szünet. A felhasználók minden időszakban más-más funkciókat érhetnek el. Van kezdetük és végül.

### Időszakok

#### Kurzus felvételi időszak

Hallgatók tudnak felvenni és leadni kurzust, ha a felvételi követelményhez hozzáadott tárgyak teljesítve vannak. A felvételnél rangsorolva vannak tanterv (milyen közel van az ajánlott félév) és az átlag alapján. 

Oktatók, ha jóváhagyásos a kurzus, jóváhagyhatják a felvételeket.

#### Szorgalmi időszak

Amennyiben a kurzus gyakorlat, az oktatónak a szorgalmi időszak végéig értékelnie kell a hallgató teljesítményét.

#### Vizsgaidőszak

Az oktató hirdethet meg vizsgákat, majd az egyes vizsgák után a résztvett hallgatóknak írhat be érdemjegyet. 

Hallgató felvehet vizsgákat.

## Nem funkcionális követelmények

- A jelszavak biztonságosan vannak eltárolva
- Input mezők kezelése biztonságos
