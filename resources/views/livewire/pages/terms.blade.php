{{-- Logika: app/Livewire/Pages/Regulamin.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state(['expandedSections' => []]);

$toggleSection = function ($section) {
    if (in_array($section, $this->expandedSections)) {
        $this->expandedSections = array_diff($this->expandedSections, [$section]);
    } else {
        $this->expandedSections[] = $section;
    }
};

?>

<div>
    <!-- Hero Section -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Regulamin
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Regulamin sklepu internetowego Zdrowe Herbaty
                </p>
            </div>
        </div>
    </section>

    <!-- Terms Section -->
    <section class="bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-8 prose prose-lg max-w-none text-gray-700 leading-relaxed">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Wstęp</h2>
                <ol class="space-y-4 text-gray-700 leading-relaxed">
                    <li>
                        Sklep internetowy <a href="http://www.zdroweherbaty.com.pl/">www.zdroweherbaty.com.pl</a>
                        prowadzony jest
                        przez firmę BiFIX Wojciech Piasecki Sp. j., z siedzibą w Górkach Małych pod adresem: Górki Małe
                        ul.
                        Dworska 33, 95 – 080 Tuszyn, zarejestrowaną w Rejestrze Przedsiębiorców prowadzonym przez Sąd
                        Rejonowy
                        dla Łodzi-Śródmieścia w Łodzi, XX Wydział Gospodarczy Krajowego Rejestru Sądowego, pod numerem
                        KRS:
                        0000072539, posiadającą numer NIP: 7710009014, numer REGON: 590031006, zwanego dalej także
                        Sprzedającym.
                    </li>
                    <li>
                        Poniższy Regulamin określa zasady realizowania usług w sklepie internetowym <a
                            href="http://www.zdroweherbaty.com.pl/">www.zdroweherbaty.com.pl</a> zwanym dalej Sklepem.
                    </li>
                    <li>
                        Niniejszy Regulamin jest udostępniony w postaci elektronicznej każdemu zainteresowanemu, poprzez
                        umieszczenie jego treści oraz pliku do pobrania na stronie:www.zdroweherbaty.com.pl . Przyjmuje
                        się, że
                        Użytkownik, zwany dalej także Klientem składając zamówienie zapoznał się z treścią Regulaminu
                        Sklepu i
                        tym samym zaakceptował postanowienia w nim zawarte.
                    </li>
                    <li>
                        Sklep prowadzi sprzedaż hurtową i detaliczną produktów na terenie Polski, realizując zamówienia
                        złożone
                        przez osoby fizyczne, osoby prawne lub jednostki organizacyjne nieposiadające osobowości
                        prawnej, którym
                        ustawa przyznaje zdolność prawną, mające swój adres zamieszkania i siedzibę na terenie Polski.
                    </li>
                    <li>
                        Minimalna kwota zamówienia to 20zł netto.
                    </li>
                    <li>
                        Regulamin stanowi integralną część zawieranej z Użytkownikiem umowy.
                    </li>
                    <li>
                        Sprzedawca zastrzega sobie prawo do dokonywania zmian Regulaminu z ważnych przyczyn, w
                        szczególności:
                        zmiany przepisów prawa, zmiany sposobów płatności i dostaw - w zakresie, w jakim te zmiany
                        wpływają na
                        realizację postanowień niniejszego Regulaminu.
                    </li>
                    <li>
                        W przypadku zawarcia na podstawie niniejszego Regulaminu umów zmiany Regulaminu nie będą w żaden
                        sposób
                        naruszać praw nabytych Klientów będących konsumentami przed dniem wejścia w życie zmian
                        Regulaminu, w
                        szczególności zmiany Regulaminu nie będą miały wpływu na już złożone zamówienia oraz realizowane
                        lub
                        wykonane umowy sprzedaży.
                    </li>
                    <li>
                        Zmiany obowiązują po upływie 7 dni od momentu udostępnienia nowej wersji Regulaminu na <a
                            href="http://www.zdroweherbaty.com.pl/">www.zdroweherbaty.com.pl</a>.
                    </li>
                </ol>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">Oferta i realizacja zamówień</h2>
                <ol class="space-y-4 text-gray-700 leading-relaxed">
                    <li>
                        Korzystając z funkcjonalności Sklepu internetowego, Klient ma możliwość poglądowego zapoznania
                        się z
                        asortymentem produktów Sprzedawcy oraz złożenia Sprzedawcy zamówienia w zakresie wybranych przez
                        Klienta
                        produktów poprzez dodanie ich do Koszyka.
                    </li>
                    <li>
                        Klient może złożyć zamówienie nie posiadając konta w Sklepie Internetowym, poprzez kliknięcie w
                        odpowiednią ikonę, jednego lub większej ilości interesujących Klienta produktów dodając je do
                        Koszyka,
                        następnie wybierając sposób dostawy i wciskając przycisk „Do kasy”. Klient w takiej sytuacji
                        zobowiązany
                        zostanie do podania swoich danych niezbędnych do realizacji zamówienia, a następnie wyboru
                        sposobu
                        płatności. Zamówienie zostanie złożone po kliknięciu w przycisk „Akceptuj i wyślij zamówienie”.
                    </li>
                    <li>
                        Ceny produktów umieszczonych na stronie internetowej Sklepu są podane w złotych polskich w
                        wariancie
                        brutto.
                    </li>
                    <li>
                        Ceny zamieszczone przy produkcie nie zawierają informacji na temat kosztów przesyłki.
                    </li>
                    <li>
                        Cena podana przy każdym towarze jest wiążąca w chwili złożenia przez Klienta zamówienia pod
                        warunkiem,
                        iż towar znajduje się na stanach magazynowych sprzedawcy.
                    </li>
                    <li>
                        Informacja o całkowitej wartości zamówienia przedstawiona jest na stronie po dokonaniu przez
                        Klienta
                        wyboru formy dostawy.
                    </li>
                    <li>
                        Po złożeniu zamówienia Klient otrzymuje e-mail, który zawiera ostateczne potwierdzenie
                        wszystkich
                        elementów zamówienia i zawarcia umowy.
                    </li>
                    <li>
                        Po złożeniu zamówienia Klient otrzyma e-mail potwierdzający otrzymanie zamówienia, w którym będą
                        się
                        znajdować dwa linki:
                    </li>
                    <ol style="list-style-type:lower-alpha">
                        <li>
                            do potwierdzenia złożonego zamówienia. Po jego kliknięciu zamówienie przejdzie do etapu
                            realizacji
                        </li>
                        <li>
                            do sprawdzenia statusu zamówienia oraz zawierający wszystkie dane do przelewu.
                        </li>
                    </ol>
                    <li>
                        Jeżeli po złożeniu zamówienia Klient nie otrzyma maila z linkiem potwierdzającego złożone
                        zamówienie
                        jest zobowiązany niezwłocznie skontaktować się ze Sklepem. Brak kontaktu ze Sklepem ze strony
                        Klienta w
                        ciągu 3 dni roboczych od dnia złożenia zamówienia jest równoznaczny z anulowaniem zamówienia.
                    </li>
                    <li>
                        Firma BiFIX Wojciech Piasecki Sp. j. zastrzega sobie prawo do weryfikacji dokonanego zamówienia
                        lub jego
                        anulowania.
                    </li>
                    <li>
                        Firma BiFIX Wojciech Piasecki Sp. j. zastrzega sobie prawo do zmiany cen towarów znajdujących
                        się w
                        ofercie, wprowadzania nowych towarów do oferty, przeprowadzania i odwoływania akcji promocyjnych
                        bądź
                        wprowadzania w nich zmian oraz wycofania poszczególnych towarów z oferty bez wcześniejszego
                        uprzedzenia.
                    </li>
                    <li>
                        Gwarancja ważna tylko z dowodem zakupu (zawsze dołączamy paragon/fakturę VAT).
                    </li>
                </ol>
                <br>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">Dostawa towaru</h2>
                <p class="text-gray-700 mb-4">Klient otrzymuje do wyboru następujące formy dostarczenia towaru:</p>
                <ol class="space-y-4 text-gray-700 leading-relaxed">
                    <li>
                        <strong>PRZESYŁKA KURIERSKA</strong> - Następuje po otrzymaniu przedpłaty na konto bankowe Firmy
                        BiFIX
                        Wojciech Piasecki Sp. j. całej należnej kwoty za towar wraz z opłatą za przesyłkę wg cennika
                        podanego
                        przy zakupie). Towar dostarczany jest zazwyczaj w ciągu 48h od daty nadania przez Sprzedającego.
                        Koszt
                        przesyłki uzależniony jest od jej wagi oraz rozmiaru i jest podany w momencie składania
                        zamówienia.
                    </li>
                    <li>
                        <strong>PRZESYŁKA KURIERSKA POBRANIOWA</strong> - Następuje po złożeniu zamówienia. Kurier
                        pobiera
                        opłatę w momencie doręczenia przesyłki. Koszt przesyłki uzależniony jest od jej wagi oraz
                        rozmiaru i
                        podany jest w momencie składania zamówienia.
                    </li>
                    <li>
                        <strong>ODBIÓR OSOBISTY</strong>
                    </li>
                </ol>
                <p>Gdy wartość zamówionego towaru przekracza kwotę <strong>5 000,00 zł netto</strong> i klient dokona
                    przedpłaty
                    wartości zamówienia na konto bankowe Firmy BiFIX Wojciech Piasecki Sp. j. , zamówienie dostarczane
                    jest na
                    koszt Sprzedającego, pod wskazany adres na terenie Polski.</p>
                <p><strong>Sprzedający nie nalicza dodatkowych opłat za pakowanie przesyłek</strong></p>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">Termin realizacji</h2>
                <ol>
                    <li>
                        W przypadku przesyłek kurierskich (niepobraniowych) towar  wysyłany jest po zaksięgowaniu wpłaty
                        na
                        koncie bankowym Sprzedającego. Towar dostarczany jest zazwyczaj w ciągu <strong>48h</strong> od
                        daty
                        nadania przez Sprzedającego.
                    </li>
                </ol>
                <p>Poza tym termin realizacji zamówienia zależny jest od dostępności danego produktu w magazynie
                    Sprzedającego.
                    W przypadku gdyby okazało się, iż zamówionego towaru nie ma aktualnie w magazynie Sprzedający
                    niezwłocznie
                    poinformuje Klienta o tym fakcie e-mailowo. </p>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">Płatności</h2>
                <ol>
                    <li>
                        <strong>PRZELEW na konto bankowe </strong>
                        <p><strong>Warianty dostawy: </strong></p>
                        <ul>
                            <li>
                                PRZESYŁKA KURIERSKA
                            </li>
                        </ul>
                        <p>Przesyłka wysyłana jest po zaksięgowaniu przelewu na koncie bankowym Sprzedającego. W
                            przypadku
                            wyboru przez Klienta tego rodzaju płatności – należy wpłacić na konto bankowe Sprzedającego
                            sumę
                            równą wartości zamówienia wraz z kosztami przesyłki.</p>
                        <p><strong>Dane do przelewu:</strong><br>
                            BiFIX Wojciech Piasecki Sp. j.<br>
                            Górki Małe ul. Dworska 33<br>
                            95 – 080 Tuszyn</p>
                        <p>Nr konta: 25 1160 2202 0000 0003 2057 7012<br>
                            W tytule przelewu wymagany jest: numer zamówienia internetowego.</p>
                    </li>
                    <li>
                        <p><strong>Płatności elektroniczne oraz płatności kartą płatniczą za pośrednictwem serwisu
                                PayU</strong>.<br>
                            Rozliczenia transakcji płatnościami elektronicznymi i kartą płatniczą przeprowadzane są
                            zgodnie z
                            wyborem Klienta za pośrednictwem serwisu PayU.pl. Obsługę płatności elektronicznych i kartą
                            płatniczą prowadzi: PayU S.A. z siedzibą w Poznaniu, ul. Grunwaldzka 186, 60-166 Poznań,
                            wpisana do
                            Rejestru Przedsiębiorców Krajowego Rejestru Sądowego pod numerem KRS: 0000274399, NIP:
                            7792308495,
                            Regon: 300523444, sąd rejestrowy: Sąd Rejonowy Poznań – Nowe Miasto i Wilda w Poznaniu, VIII
                            Wydział
                            Gospodarczy Krajowego Rejestru Sądowego.</p>
                        <p>Przesyłka wysyłana jest po zaksięgowaniu przelewu na koncie bankowym Sprzedającego.</p>
                    </li>
                    <li>
                        <strong>POBRANIE </strong>
                        <p><strong>Wariant dostawy:</strong></p>
                        <ul>
                            <li>
                                Przesyłka kurierska pobraniowa
                            </li>
                        </ul>
                        <p>Przesyłka wysyłana jest po złożeniu zamówienia. Za przesyłkę Klient płaci kurierowi w
                            momencie
                            odbierania przesyłki. </p>
                    </li>
                    <li>
                        <strong>GOTÓWKA </strong>
                        <p><strong>Wariant dostawy</strong></p>
                        <ul>
                            <li>
                                Odbiór osobisty<br>
                                <p>Klient odbiera towar osobiście - płatność gotówką przy odbiorze.</p>
                            </li>
                        </ul>
                    </li>
                </ol>
                <p class="text-gray-700 mb-4"><strong><u>Do każdego zamówienia wystawiany jest paragon bądź faktura
                            VAT.</u></strong></p>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">ODSTĄPIENIE OD UMOWY, REKLAMACJE , ZWRoT TOWARÓW
                </h2>
                <p class="text-gray-700 mb-4"><strong>Odstąpienie od umowy</strong></p>
                <ol>
                    <li>
                        <p>Klientowi będącemu konsumentem w rozumieniu przepisów Kodeksu cywilnego przysługuje prawo
                            odstąpienia
                            od umowy i możliwość zwrotu towaru.<br>
                            Klient może odstąpić od umowy bez podania przyczyn, składając stosowne oświadczenie na
                            piśmie oraz
                            dokument (do pobrania poniżej) w terminie 10 dni od dnia otrzymania towaru. Zwracany towar
                            musi być
                            fabrycznie zapakowany, nie rozpakowany z oryginalnego opakowania.</p>
                        <p>Dokument oświadczenia o odstąpieniu od umowy <a
                                href="{{ asset('/formularz_odstąpienia_od_umowy_Bifix.pdf') }}">pobierz</a></p>
                    </li>
                    <li>
                        Do zachowania terminu wystarczy wysłanie oświadczenia o odstąpieniu od umowy sprzedaży przed
                        upływem ww.
                        terminu. Oświadczenie o odstąpieniu od umowy sprzedaży może zostać złożone:
                        <ol type="a">
                            <li>pisemnie na adres: Górki Małe, ul. Dworska 33, 95-080 Tuszyn</li>
                            <li>w formie wiadomości e-mail na adres poczty elektronicznej:
                                <a href="mailto:reklamacjasklep@bifix.pl">reklamacjasklep@bifix.pl</a>
                            </li>
                        </ol>
                    </li>
                    <li>
                        Klient może zrezygnować z całości zamówienia lub z jego części.
                    </li>
                    <li>
                        <p>Klient ma obowiązek niezwłocznie, nie później niż w terminie 14 dni kalendarzowych od dnia, w
                            którym
                            odstąpił od umowy sprzedaży, zwrócić towar Sprzedawcy lub przekazać go osobie upoważnionej
                            przez
                            Sprzedawcę do odbioru, chyba że Sprzedawca zaproponował, że sam odbierze towar. Do
                            zachowania
                            terminu wystarczy odesłanie towaru przed jego upływem. Klient może zwrócić towar na adres:
                        </p>
                        <p><strong>BiFIX Wojciech Piasecki Sp. j.</strong>, Górki Małe, ul. Dworska 33, 95 – 080 Tuszyn.
                            z dopiskiem <strong>"Odstąpienie od umowy"</strong>.</p>
                    </li>
                    <li>
                        Klient ma obowiązek należycie zabezpieczyć odsyłany towar, tj. w taki sposób, aby zapobiec jego
                        uszkodzeniu w transporcie.
                    </li>
                    <li>
                        Jeżeli dostarczony Sprzedawcy w wyniku odstąpienia przez Klienta towar jest niekompletny, nosi
                        ślady
                        użytkownika, wykraczające poza zwykły zarząd rzeczą lub jest uszkodzony, Sprzedawca jest
                        uprawniony bądź
                        do odmowy przyjęcia towaru bądź do obniżenia zwracanej kwoty o wartość towaru niekompletnego,
                        noszącego
                        ślady użytkowania lub uszkodzonego.
                    </li>
                    <li>
                        W przypadku odstąpienia od umowy zawartej na odległość, umowę tę uważa się za niezawartą. Jeżeli
                        Klient
                        złożył oświadczenie o odstąpieniu od umowy zanim Sprzedający zrealizował zamówienie, umowa
                        przestaje
                        obowiązywać.
                    </li>
                    <li>
                        Oświadczenie o odstąpieniu od umowy sprzedaży złożone po terminie wskazanym w ust. 1 niniejszego
                        paragrafu nie wywołuje skutków prawnych.
                    </li>
                    <li>
                        Sprzedawca ma obowiązek niezwłocznie, nie później niż w terminie 14 dni kalendarzowych od dnia
                        otrzymania oświadczenia Klienta o odstąpieniu od umowy, zwrócić Klientowi wszystkie dokonane
                        przez niego
                        płatności, w tym koszty dostawy towaru (z wyjątkiem dodatkowych kosztów wynikających z wybranego
                        przez
                        Klienta sposobu dostawy innego niż najtańszy zwykły sposób dostawy dostępny w Sklepie
                        internetowym).
                        Sprzedawca dokonuje zwrotu płatności przy użyciu takiego samego sposobu płatności, jakiego użył
                        Klient,
                        chyba że Klient wyraźnie zgodził się na inny sposób zwrotu, który nie wiąże się dla niego z
                        żadnymi
                        kosztami. W przypadku płatności kartą płatniczą, Sprzedawca dokona zwrotu na rachunek bankowy
                        przypisany
                        do danej karty płatniczej.
                    </li>
                    <li>
                        Jeżeli Klient wybrał sposób dostarczenia rzeczy inny niż najtańszy zwykły sposób dostarczenia
                        oferowany
                        przez Sprzedawcę, Sprzedawca nie jest zobowiązany do zwrotu Klientowi poniesionych przez niego
                        dodatkowych kosztów.
                    </li>
                    <li>
                        Klient ponosi tylko bezpośrednie koszty zwrotu rzeczy.
                    </li>
                    <li>
                        Sprzedawca może wstrzymać się ze zwrotem płatności otrzymanych od Klienta do chwili otrzymania
                        towaru z
                        powrotem lub dostarczenia przez Klienta dowodu jego odesłania, w zależności od tego, które
                        zdarzenie
                        nastąpi wcześniej.
                    </li>
                    <li>
                        Do zwracanego Produktu należy dołączyć oryginał/kopię dokumentu sprzedaży (faktury lub paragonu)
                        oraz
                        wypełniony i podpisany formularz zwrotu towaru.
                    </li>
                    <li>
                        <p><strong>Sprzedający nie przyjmuje przesyłek za pobraniem.</strong> Do przesyłki zwrotnej
                            należy
                            dołączyć pisemne oświadczenie o odstąpieniu od umowy, dokument zwrotu (do pobrania poniżej)
                            zawierający dane teleadresowe Klienta, konto bankowe na jakie ma zostać przekazana kwota za
                            towar.
                            Aby dokonać zwrotu należy wydrukować i wypełnić dokument zwrotu oraz przesłać towar wraz z
                            oryginałem faktury bądź paragonem na adres Sprzedającego.</p>
                        <p>Dokument do zwrotu <a href="{{ asset('/Zwrot.pdf') }}">pobierz</a>.</p>
                    </li>
                    <li>
                        Po otrzymaniu towaru Sprzedający zastrzega sobie okres 14 dniowy na sprawdzenie towaru,
                        zgodności danych
                        personalnych Klienta oraz analizę warunków zwrotu w odniesieniu do konkretnej transakcji.
                    </li>
                    <li>
                        W przypadku niezgodności towaru oraz niespełnieniu warunków zwrotu/ odstąpienia od umowy towar
                        zostanie
                        odesłany na koszt Klienta.
                    </li>
                </ol>
                <p class="text-gray-700 mb-4 mt-6"><strong>Reklamacje</strong></p>
                <ol>
                    <li>
                        W przypadku nie otrzymania towaru zgodnego z zamówieniem należy niezwłocznie skontaktować się ze
                        Sprzedającym tylko i wyłącznie poprzez e-mail: <a
                            href="mailto:reklamacjasklep@bifix.pl">reklamacjasklep@bifix.pl</a>. Po
                        otrzymaniu takiej informacji zamówienie zostanie zweryfikowane ponownie oraz Klient otrzyma
                        informację w
                        ciągu 48 godzin, dotyczącą formy dostarczenia/wymiany towaru na zgodny z zamówieniem.
                    </li>
                    <li>
                        Natomiast towar niezgodny z zamówieniem klient odsyła na koszt Sprzedającego. Towar nie może
                        nosić
                        śladów uszkodzeń mechanicznych, samodzielnych napraw/demontażu.
                    </li>
                    <li>
                        Na towary dostępne w Sklepie udzielona jest gwarancja Producenta 24 m-ce od daty produkcji
                        umieszczonej
                        na opakowaniu herbaty. Jeżeli otrzymany towar posiada uszkodzenia fabryczne, wady produkcyjne,
                        wady
                        techniczne lub uszkodzenia powstałe podczas transportu, może zostać odesłany przez Klienta. W
                        takiej
                        sytuacji Klient zobowiązany jest do wcześniejszego kontaktu telefonicznego lub e-mailowego ze
                        Sprzedającym.
                    </li>
                    <li>
                        Do odsyłanego towaru należy dołączyć wydrukowany i wypełniony formularz reklamacji (do pobrania
                        poniżej)
                        oraz oryginał dokumentu zakupu ( faktura bądź paragon).
                    </li>
                    <li><strong>Dokument reklamacji</strong> <a href="{{ asset('/Reklamacja.pdf') }}">pobierz</a></li>
                    <li>Reklamowany towar nie może nosić śladów uszkodzeń mechanicznych, samodzielnych napraw/demontażu.
                        Towar
                        należy odesłać na adres:</li>
                    <li><strong>BiFIX Wojciech Piasecki Sp. j.</strong>, Górki Małe, ul. Dworska 33, 95 – 080 Tuszyn. z
                        dopiskiem <strong>&quot;REKLAMACJA&quot;</strong></li>
                    <li>Sprzedający nie przyjmuje żadnych przesyłek odsyłanych za pobraniem. Sprzedający zobowiązuje się
                        do
                        nieprzekraczania terminu 14 dni roboczych (od daty przyjęcia reklamowanego towaru do sklepu) na
                        rozpatrzenie reklamacji. Jeżeli reklamacja zostanie uznana przez Dział Reklamacji Producenta,
                        towar
                        zostanie wymieniony na pełnowartościowy; lub, jeśli nie będzie to możliwe (np. z powodu
                        wyczerpania
                        stanów magazynowych), Sprzedający zwróci Klientowi równowartość ceny produktu. Jeśli reklamacja
                        zostanie
                        odrzucona przez Dział Reklamacji jako nieuzasadniona, towar może zostać odesłany do Klienta na
                        jego
                        koszt, z możliwością uwzględnienia kosztów poniesionych przez Sprzedającego.</li>
                </ol>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">OCHRONA PRYWATNOŚCI I INNE PRAWA</h2>
                <p>Firma BiFIX Wojciech Piasecki Sp. j. nie ponosi odpowiedzialności za szkody związane z wadliwym
                    funkcjonowaniem systemów teleinformatycznych lub telekomunikacyjnych, jak również za szkody powstałe
                    z
                    powodu wadliwego funkcjonowania urządzeń lub jego oprogramowania po stronie Klienta. </p>
                <p>Złożenie zamówienia jest równoznaczne ze zgodą na przechowywanie i przetwarzanie przez firmę BiFIX
                    Wojciech
                    Piasecki Sp. j. danych osobowych zawartych w zamówieniu zgodnie z obowiązującymi przepisami ustawy o
                    ochronie danych osobowych Dz. U. nr 133 poz. 883. </p>
                <p>Prezentacja zawartości sklepu BiFIX Wojciech Piasecki Sp. j. nie stanowi oferty handlowej w
                    rozumieniu art.
                    543 Kodeksu Cywilnego.</p>
                <p>Wszystkie wymienione produkty i nazwy są używane wyłącznie w celach identyfikacyjnych i mogą być
                    zastrzeżonymi znakami towarowymi odpowiednich właścicieli.</p>
                <p>Kopiowanie w całości lub fragmentów informacji zawartych na stronie sklepu www.zdroweherbaty.com.pl
                    zarówno w
                    postaci tekstowej jak i graficznej bez wyraźnej, pisemnej zgody firmy BiFIX Wojciech Piasecki Sp. j.
                    jest
                    zabronione.</p>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">Klauzula informacyjna o przetwarzaniu danych
                    osobowych dla Kontrahentów firmy BIFIX Wojciech
                    Piasecki sp.j.
                </h2>
                <p>
                    Zgodnie z rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 z
                    27.04.2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem danych
                    osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy
                    95/46/WE (dalej RODO) (Dz.Urz. UE L 119, s. 1), w związku z naszą współpracą, szanując
                    Twoją prywatność oraz dbając o to, abyś wiedział kto i w jaki sposób przetwarza Twoje dane
                    osobowe, poniżej przedstawiam informacje, które pomogą Ci to ustalić:
                </p>
                <ol>
                    <li>
                        Administratorem Twoich danych osobowych jest BIFIX Wojciech Piasecki spółka jawna z
                        siedzibą w Górkach Małych, ul. Dworska 33, 98-080 Tuszyn, zarejestrowana w rejestrze
                        przedsiębiorców prowadzonym przez Sąd Rejonowy dla Łodzi-Śródmieścia w Łodzi, XX
                        Wydział Gospodarczy Krajowego Rejestru Sądowego pod numerem KRS: 0000072539,
                        NIP: 7710009014 (dalej „Administrator”). Korespondencję związaną z przetwarzaniem
                        danych osobowych należy kierować na wskazany w zdaniu poprzedzającym adres
                        Administratora lub adres e-mail: <a href="mailto:bifix@bifix.pl">bifix@bifix.pl</a>
                    </li>
                    <li>
                        Twoje dane osobowe przetwarzane będą w kilku różnych celach tj. dla prawidłowej
                        realizacji umowy, marketingu bezpośredniego własnych produktów oraz usług, celem
                        wykonania obowiązków prawnych, w tym podatkowych, a także mogą być przetwarzane
                        dla dochodzenia roszczeń wynikających z przepisów prawa cywilnego oraz obrony
                        przed takimi roszczeniami, jeśli takie się pojawią.
                    </li>
                    <li>
                        Podstawą prawną przetwarzania danych osobowych jest:
                        <ol type="a">
                            <li>
                                art. 6 ust. 1 pkt. b) RODO w zakresie niezbędnym do wykonania umowy;
                            </li>
                            <li>
                                art. 6 ust. 1 pkt c) RODO, gdy jest to niezbędne do realizacji obowiązków na nas
                                ciążących takich jak prowadzenie rozliczeń finansowych, w tym podatkowych;
                            </li>
                            <li>
                                art. 6 ust. 1 pkt f) RODO, gdy jest to nieodzowne dla realizacji celów wynikających z
                                naszych prawnie uzasadnionych interesów, takich jak ewentualna konieczność
                                odpierania lub realizacji roszczeń cywilnoprawnych, a także w celu kierowania treści
                                marketingowych o produktach lub usługach Administratora. W trakcie trwania
                                umowy podstawą prawną kierowania treści marketingowych jest uzasadniony
                                interes prawny Administratora - art. 6 ust. 1 pkt. f) RODO, a po zakończeniu umowy
                                Twoja zgoda - art. 6 ust. 1 pkt. a) RODO.
                            </li>
                        </ol>
                    </li>
                    <li>
                        Twoje dane osobowe nie będą przekazywane do państwa trzeciego/organizacji
                        międzynarodowej.
                    </li>
                    <li>
                        Twoje dane osobowe będą przechowywane przez okres niezbędny do konkretnego
                        przetwarzania danych. Z pewnością dane będą przetwarzane przez okres trwania
                        łączącej nas umowy, jak również przez okres trwania wymagalności ewentualnych
                        roszczeń z tym związanych.
                    </li>
                    <li>
                        Posiadasz prawo żądania dostępu do treści swoich danych oraz ich sprostowania,
                        usunięcia, ograniczenia przetwarzania, prawo do przenoszenia danych, prawo
                        wniesienia sprzeciwu.
                    </li>
                    <li>
                        Przysługuje Ci prawo wniesienia w dowolnym momencie sprzeciwu względem
                        przetwarzania danych osobowych w celu kierowania do Ciebie treści marketingowych.
                    </li>
                    <li>
                        W przypadku gdy przetwarzanie danych osobowych odbywa się na podstawie zgody na
                        przetwarzanie danych osobowych, masz prawo do cofnięcia wyrażonej zgody na
                        przetwarzanie danych w dowolnym momencie bez wpływu na zgodność z prawem
                        przetwarzania, którego dokonano na podstawie zgody przed jej cofnięciem, poprzez
                        przesłanie wiadomości e-mail na adres: <a href="mailto:bifix@bifix.pl">bifix@bifix.pl</a>
                    </li>
                    <li>
                        Masz prawo wniesienia skargi do organu nadzorczego, którym jest Prezes Urzędu
                        Ochrony Danych Osobowych, gdy uznasz, że przetwarzanie danych osobowych narusza
                        przepisy prawa powszechnie obowiązującego, w tym prawa unijnego w zakresie
                        ochrony danych osobowych.
                    </li>
                    <li>
                        W sytuacji, gdy przetwarzanie danych osobowych odbywa się na podstawie zgody,
                        podanie przez Ciebie danych osobowych ma charakter dobrowolny. W przypadku gdy
                        podstawę przetwarzania danych osobowych stanowi zawarta między stronami umowa,
                        podanie przez Ciebie danych osobowych jest dobrowolne, jednakże konieczne do
                        realizacji zawartej umowy.
                    </li>
                    <li>
                        Twoje dane osobowe nie będą przetwarzane w sposób zautomatyzowany, w tym
                        również w formie profilowania.
                    </li>
                </ol>

                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">POZASĄDOWE SPOSOBY ROZPATRYWANIA REKLAMACJI I
                    DOCHODZENIA ROSZCZEŃ</h2>
                <ol>
                    <li>
                        Niniejszy paragraf Regulaminu dotyczy wyłącznie Klientów będących konsumentami w rozumieniu
                        przepisów
                        Kodeksu cywilnego.
                    </li>
                    <li>
                        Szczegółowe informacje dotyczące możliwości skorzystania przez Klienta będącego konsumentem z
                        pozasądowych sposobów rozpatrywania reklamacji i dochodzenia roszczeń oraz zasady dostępu do
                        tych
                        procedur dostępne są na stronie internetowej Urzędu Ochrony Konkurencji i Konsumentów pod
                        adresem: <a href="https://uokik.gov.pl/pozasadowe_rozwiazywanie_sporow_konsumenckich.php"
                            target="_blank">https://uokik.gov.pl/pozasadowe_rozwiazywanie_sporow_konsumenckich.php</a>
                    </li>
                    <li>
                        Przy Prezesie Urzędu Ochrony Konkurencji i Konsumentów działa także punkt kontaktowy (telefon:
                        22 55 60
                        333, email: kontakt.adr@uokik.gov.pl lub adres pisemny: Pl. Powstańców Warszawy 1, 00-030
                        Warszawa),
                        którego zadaniem jest między innymi udzielanie pomocy Konsumentom w sprawach dotyczących
                        pozasądowego
                        rozwiązywania sporów konsumenckich.
                    </li>
                    <li>
                        Konsument posiada następujące przykładowe możliwości skorzystania z pozasądowych sposobów
                        rozpatrywania
                        reklamacji i dochodzenia roszczeń:
                        <ol type="a">
                            <li>
                                wniosek o rozstrzygnięcie sporu do stałego polubownego sądu konsumenckiego (więcej
                                informacji na
                                stronie: <a href="http://www.spsk.wiih.org.pl">http://www.spsk.wiih.org.pl</a>);
                            </li>
                            <li>
                                wniosek w sprawie pozasądowego rozwiązania sporu do wojewódzkiego inspektora Inspekcji
                                Handlowej
                                (więcej informacji na stronie inspektora właściwego ze względu na miejsce wykonywania
                                działalności gospodarczej przez Sprzedawcę);
                            </li>
                            <li>
                                pomoc powiatowego (miejskiego) rzecznika konsumentów lub organizacji społecznej, do
                                której zadań
                                statutowych należy ochrona konsumentów (m.in. Federacja Konsumentów, Stowarzyszenie
                                Konsumentów
                                Polskich). Porady udzielane są między innymi mailowo pod adresem
                                porady@dlakonsumentow.pl oraz
                                pod numerem infolinii konsumenckiej 801 440 220 (infolinia czynna w dni robocze, w
                                godzinach
                                8:00 - 18:00, opłata za połączenie według taryfy operatora).
                            </li>
                        </ol>
                    </li>
                    <li>
                        Pod adresem <a href="http://ec.europa.eu/consumers/odr"
                            target="_blank">http://ec.europa.eu/consumers/odr</a> dostępna jest platforma internetowego
                        systemu
                        rozstrzygania sporów pomiędzy Konsumentami i przedsiębiorcami na szczeblu unijnym (platforma
                        ODR).
                        Platforma ODR stanowi interaktywną i wielojęzyczną stronę internetową z punktem kompleksowej
                        obsługi dla
                        Konsumentów i przedsiębiorców dążących do pozasądowego rozstrzygnięcia sporu dotyczącego
                        zobowiązań
                        umownych wynikających z internetowej umowy sprzedaży lub umowy o świadczenie usług (więcej
                        informacji na
                        stronie samej platformy lub pod adresem internetowym Urzędu Ochrony Konkurencji i Konsumentów:
                        <a href="https://uokik.gov.pl/spory_konsumenckie_faq_platforma_odr.php"
                            target="_blank">https://uokik.gov.pl/spory_konsumenckie_faq_platforma_odr.php</a>).
                    </li>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">KONTAKT</h2>
                <p class="text-gray-700 mb-4">Klient może skontaktować się z Sprzedającym w jeden z poniżej wskazanych
                    sposobów:</p>
                <ol type="a">
                    <li>
                        korespondencyjnie: BIFIX Wojciech Piasecki spółka jawna z siedzibą w Górkach Małych, ul. Dworska
                        33,
                        98-080 Tuszyn;
                    </li>
                    <li>
                        e-mail: <a href="mailto:bifix@bifix.pl">bifix@bifix.pl</a>
                    </li>
                    <li>
                        telefonicznie: 42 614 40 58 wew. 155
                    </li>
                    <li>
                        fax. 42 614 41 20
                    </li>
                </ol>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8">POSTANOWIENIA KOŃCOWE</h2>
                <ol>
                    <li>
                        Niniejszy Regulamin obowiązuje od dnia 11.04.2020 r.
                    </li>
                    <li>
                        Umowy zawierane poprzez Sklep Internetowy zawierane są w języku polskim.
                    </li>
                    <li>
                        W sprawach nieuregulowanych w niniejszym Regulaminie mają zastosowanie powszechnie obowiązujące
                        przepisy
                        prawa polskiego, w szczególności:
                        <ol type="a">
                            <li>
                                Kodeksu cywilnego;
                            </li>
                            <li>
                                ustawy o świadczeniu usług drogą elektroniczną z dnia 18 lipca 2002 r. (Dz.U. 2002 nr
                                144, poz.
                                1204 ze zm.);
                            </li>
                            <li>
                                ustawy o prawach konsumenta z dnia 30 maja 2014 r. (Dz.U. 2014 r. poz. 827 ze zm.);
                            </li>
                            <li>
                                oraz inne właściwe przepisy powszechnie obowiązującego prawa.
                            </li>
                        </ol>
                    </li>
                    <li>
                        Sprzedawca Sklepu Internetowego nie ponosi odpowiedzialności za szkody i krzywdy wynikające z
                        niewłaściwego działania serwera, na którym umieszczona jest platforma internetowa Sklepu.
                        Dotyczy to w
                        szczególności skutków błędów w działaniu stron internetowych, braku dostępu do nich oraz innych
                        awarii,
                        uszkodzeń lub zakłócenia w funkcjonowaniu usług internetowych.
                    </li>
                    <li>
                        Sprzedawca zastrzega sobie prawo do incydentalnego, częściowego lub całkowitego wyłączenia
                        Sklepu
                        Internetowego w celu jego konserwacji, ulepszania bądź poszerzenia usług.
                    </li>
                    <li>
                        Sprzedawca nie ponosi odpowiedzialności za szkody i krzywdy spowodowane jego działaniem lub
                        zaniechaniem
                        wynikającym z otrzymanych od Klienta nieprawidłowych danych.
                    </li>
                </ol>
            </div>
        </div>
    </section>
</div>
