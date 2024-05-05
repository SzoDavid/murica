Felhasználó(<u>kód</u>, születésiIdő, név, jelszó, email)

Admin(*<u>kód</u>*)

Hallgató(*<u>kód</u>*, <u>kezdésÉve</u>)

Szak(<u>név</u>, típus, időtartam)

MilyenSzakon(<u>*Hallgató.kód*</u>, *<u>kezdésÉve</u>*, <u>*Szak.név*</u>)

Tárgy(<u>kód</u>, név, jóváhagyásos, kredit, típus, előfeltételKód, melyik, mihez)

Terem(<u>kód</u>, férőhely)

Kurzus(<u>kód</u>, férőhely, időpont, félév, *Terem.kód*)

FelvettKurzus(*<u>Hallgató.kód</u>*, *<u>kezdésÉve</u>*, *<u>Kurzus.kód</u>*, érdemjegy, jóváhagyva)

KurzustOktat(<u>*Felhasználó.kód*</u>, <u>*Kurzus.kód*</u>)

Vizsga(<u>kód</u>, időpont, vizsgáztató, férőhely, *Terem.kód*, *Tárgy.kód*)

FelvettVizsga(*<u>Hallgató.kód</u>*, *<u>kezdésÉve</u>*, *<u>Vizsga.kód</u>*)
