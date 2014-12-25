<?php

/**
 * @author Pavel Sedlák 2009-2013
 * @author Mikuláš Dítě
 *
 * @url: http://www.pteryx.net/sklonovani.html
 */
class Inflection
{

	/**
	 * @var bool
	 */
	private $isDebugMode = FALSE;

	/**
	 * @var string enum (0|m|f|s)
	 */
	protected $gender = '0';

	/**
	 * Inflection patterns
	 * Pattern (2nd key) may either
	 * - start with '-': postfix match
	 *   example: '-lo' matches 'kolo'
	 * - start with anything else: full string match
	 *   example: 'lo' does not match 'kolo', but matches 'lo'
	 *
	 * Postfixes may contain numbers, which map to character group matched
	 * in pattern (numbered from 0 from end), and contain two versions
	 * delimited by '/' for neživotný and životný rod respectively.
	 *
	 * @var array {
	 *   @var string char 0|m|f|s,
	 *   @var string pattern for nominative,
	 *   @var string postfix for genitive,
	 *   ... postfix for 5 remaining singular and 7 plural
	 * }
	 */
	protected $patterns = [
		// hořký
		["m", "-ký", "kého", "kému", "ký/kého", "ký", "kém", "kým", "-ké/-cí", "kých", "kým", "ké", "-ké/-cí", "kých", "kými"],
		// modrý
		["m", "-rý", "rého", "rému", "rý/rého", "rý", "rém", "rým", "-ré/-ří", "rých", "rým", "ré", "-ré/-ří", "rých", "rými"],
		// jednodychý
		["m", "-chý", "chého", "chému", "chý/chého", "chý", "chém", "chým", "-ché/-ší", "chých", "chým", "ché", "-ché/-ší", "chých", "chými"],
		// strohý
		["m", "-hý", "hého", "hému", "hý/hého", "hý", "hém", "hým", "-hé/-zí", "hých", "hým", "hé", "-hé/-zí", "hých", "hými"],
		// jedlý
		["m", "-ý", "ého", "ému", "ý/ého", "ý", "ém", "ým", "-é/-í", "ých", "ým", "é", "-é/-í", "ých", "ými"],
		// spící
		["m", "-[aeěií]cí", "0cího", "0címu", "0cí/0cího", "0cí", "0cím", "0cím", "0cí", "0cích", "0cím", "0cí", "0cí", "0cích", "0cími"],
		["f", "-[aeěií]cí", "0cí", "0cí", "0cí", "0cí", "0cí", "0cí", "0cí", "0cích", "0cím", "0cí", "0cí", "0cích", "0cími"],
		["s", "-[aeěií]cí", "0cího", "0címu", "0cí/0cího", "0cí", "0cím", "0cím", "0cí", "0cích", "0cím", "0cí", "0cí", "0cích", "0cími"],
		// svatební
		["m", "-[bcčdhklmnprsštvzž]ní", "0ního", "0nímu", "0ní/0ního", "0ní", "0ním", "0ním", "0ní", "0ních", "0ním", "0ní", "0ní", "0ních", "0ními"],
		["f", "-[bcčdhklmnprsštvzž]ní", "0ní", "0ní", "0ní", "0ní", "0ní", "0ní", "0ní", "0ních", "0ním", "0ní", "0ní", "0ních", "0ními"],
		["s", "-[bcčdhklmnprsštvzž]ní", "0ního", "0nímu", "0ní/0ního", "0ní", "0ním", "0ním", "0ní", "0ních", "0ním", "0ní", "0ní", "0ních", "0ními"],
		// držitel
		["m", "-[i]tel", "0tele", "0teli", "0tele", "0tel", "0teli", "0telem", "0telé", "0telů", "0telům", "0tele", "0telé", "0telích", "0teli"],
		// přítel
		["m", "-[í]tel", "0tele", "0teli", "0tele", "0tel", "0teli", "0telem", "átelé", "áteli", "átelům", "átele", "átelé", "átelích", "áteli"],
		// malé
		["s", "-é", "ého", "ému", "é", "é", "ém", "ým", "-á", "ých", "ým", "á", "á", "ých", "ými"],
		// malá
		["f", "-á", "é", "é", "ou", "á", "é", "ou", "-é", "ých", "ým", "é", "é", "ých", "ými"],

		["-", "já", "mne", "mně", "mne/mě", "já", "mně", "mnou", "my", "nás", "nám", "nás", "my", "nás", "námi"],
		["-", "ty", "tebe", "tobě", "tě/tebe", "ty", "tobě", "tebou", "vy", "vás", "vám", "vás", "vy", "vás", "vámi"],
		["-", "my", "", "", "", "", "", "", "my", "nás", "nám", "nás", "my", "nás", "námi"],
		["-", "vy", "", "", "", "", "", "", "vy", "vás", "vám", "vás", "vy", "vás", "vámi"],
		["m", "on", "něho", "mu/jemu/němu", "ho/jej", "on", "něm", "ním", "oni", "nich", "nim", "je", "oni", "nich", "jimi/nimi"],
		["m", "oni", "", "", "", "", "", "", "oni", "nich", "nim", "je", "oni", "nich", "jimi/nimi"],
		["f", "ony", "", "", "", "", "", "", "ony", "nich", "nim", "je", "ony", "nich", "jimi/nimi"],
		["s", "ono", "něho", "mu/jemu/němu", "ho/jej", "ono", "něm", "ním", "ona", "nich", "nim", "je", "ony", "nich", "jimi/nimi"],
		["f", "ona", "ní", "ní", "ji", "ona", "ní", "ní", "ony", "nich", "nim", "je", "ony", "nich", "jimi/nimi"],
		["m", "ten", "toho", "tomu", "toho", "ten", "tom", "tím", "ti", "těch", "těm", "ty", "ti", "těch", "těmi"],
		["f", "ta", "té", "té", "tu", "ta", "té", "tou", "ty", "těch", "těm", "ty", "ty", "těch", "těmi"],
		["s", "to", "toho", "tomu", "toho", "to", "tom", "tím", "ta", "těch", "těm", "ta", "ta", "těch", "těmi"],

		// přivlastňovací zájmena
		["m", "můj", "mého", "mému", "mého", "můj", "mém", "mým", "mí", "mých", "mým", "mé", "mí", "mých", "mými"],
		["f", "má", "mé", "mé", "mou", "má", "mé", "mou", "mé", "mých", "mým", "mé", "mé", "mých", "mými"],
		["f", "moje", "mé", "mé", "mou", "má", "mé", "mou", "moje", "mých", "mým", "mé", "mé", "mých", "mými"],
		["s", "mé", "mého", "mému", "mé", "moje", "mém", "mým", "mé", "mých", "mým", "má", "má", "mých", "mými"],
		["s", "moje", "mého", "mému", "moje", "moje", "mém", "mým", "moje", "mých", "mým", "má", "má", "mých", "mými"],

		["m", "tvůj", "tvého", "tvému", "tvého", "tvůj", "tvém", "tvým", "tví", "tvých", "tvým", "tvé", "tví", "tvých", "tvými"],
		["f", "tvá", "tvé", "tvé", "tvou", "tvá", "tvé", "tvou", "tvé", "tvých", "tvým", "tvé", "tvé", "tvých", "tvými"],
		["f", "tvoje", "tvé", "tvé", "tvou", "tvá", "tvé", "tvou", "tvé", "tvých", "tvým", "tvé", "tvé", "tvých", "tvými"],
		["s", "tvé", "tvého", "tvému", "tvého", "tvůj", "tvém", "tvým", "tvá", "tvých", "tvým", "tvé", "tvá", "tvých", "tvými"],
		["s", "tvoje", "tvého", "tvému", "tvého", "tvůj", "tvém", "tvým", "tvá", "tvých", "tvým", "tvé", "tvá", "tvých", "tvými"],

		["m", "náš", "našeho", "našemu", "našeho", "náš", "našem", "našim", "naši", "našich", "našim", "naše", "naši", "našich", "našimi"],
		["f", "naše", "naší", "naší", "naši", "naše", "naší", "naší", "naše", "našich", "našim", "naše", "naše", "našich", "našimi"],
		["s", "naše", "našeho", "našemu", "našeho", "naše", "našem", "našim", "naše", "našich", "našim", "naše", "naše", "našich", "našimi"],

		["m", "váš", "vašeho", "vašemu", "vašeho", "váš", "vašem", "vašim", "vaši", "vašich", "vašim", "vaše", "vaši", "vašich", "vašimi"],
		["f", "vaše", "vaší", "vaší", "vaši", "vaše", "vaší", "vaší", "vaše", "vašich", "vašim", "vaše", "vaše", "vašich", "vašimi"],
		["s", "vaše", "vašeho", "vašemu", "vašeho", "vaše", "vašem", "vašim", "vaše", "vašich", "vašim", "vaše", "vaše", "vašich", "vašimi"],

		["m", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho"],
		["f", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho"],
		["s", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho", "jeho"],

		["m", "její", "jejího", "jejímu", "jejího", "její", "jejím", "jejím", "její", "jejích", "jejím", "její", "její", "jejích", "jejími"],
		["s", "její", "jejího", "jejímu", "jejího", "její", "jejím", "jejím", "její", "jejích", "jejím", "její", "její", "jejích", "jejími"],
		["f", "její", "její", "její", "její", "její", "její", "její", "její", "jejích", "jejím", "její", "její", "jejích", "jejími"],

		["m", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich"],
		["s", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich"],
		["f", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich", "jejich"],

		// výjimky (zvl. běžná slova)
		["m", "-bůh", "boha", "bohu", "boha", "bože", "bohovi", "bohem", "bozi/bohové", "bohů", "bohům", "bohy", "bozi/bohové", "bozích", "bohy"],
		["m", "-pan", "pana", "panu", "pana", "pane", "panu", "panem", "páni/pánové", "pánů", "pánům", "pány", "páni/pánové", "pánech", "pány"],
		["s", "moře", "moře", "moři", "moře", "moře", "moři", "mořem", "moře", "moří", "mořím", "moře", "moře", "mořích", "moři"],
		["-", "dveře", "", "", "", "", "", "", "dveře", "dveří", "dveřím", "dveře", "dveře", "dveřích", "dveřmi"],
		["-", "housle", "", "", "", "", "", "", "housle", "houslí", "houslím", "housle", "housle", "houslích", "houslemi"],
		["-", "šle", "", "", "", "", "", "", "šle", "šlí", "šlím", "šle", "šle", "šlích", "šlemi"],
		["-", "muka", "", "", "", "", "", "", "muka", "muk", "mukám", "muka", "muka", "mukách", "mukami"],
		["s", "ovoce", "ovoce", "ovoci", "ovoce", "ovoce", "ovoci", "ovocem", "", "", "", "", "", "", ""],
		["m", "humus", "humusu", "humusu", "humus", "humuse", "humusu", "humusem", "humusy", "humusů", "humusům", "humusy", "humusy", "humusech", "humusy"],
		["m", "-vztek", "vzteku", "vzteku", "vztek", "vzteku", "vzteku", "vztekem", "vzteky", "vzteků", "vztekům", "vzteky", "vzteky", "vztecích", "vzteky"],
		["m", "-dotek", "doteku", "doteku", "dotek", "doteku", "doteku", "dotekem", "doteky", "doteků", "dotekům", "doteky", "doteky", "dotecích", "doteky"],
		["f", "-hra", "hry", "hře", "hru", "hro", "hře", "hrou", "hry", "her", "hrám", "hry", "hry", "hrách", "hrami"],
		["m", "zeus", "dia", "diovi", "dia", "die", "diovi", "diem", "diové", "diů", "diům", NULL, "diové", NULL, NULL],
		["f", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol", "nikol"],

		// číslovky
		["-", "-tdva", "tidvou", "tidvoum", "tdva", "tdva", "tidvou", "tidvěmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tdvě", "tidvou", "tidvěma", "tdva", "tdva", "tidvou", "tidvěmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-ttři", "titří", "titřem", "ttři", "ttři", "titřech", "titřemi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tčtyři", "tičtyřech", "tičtyřem", "tčtyři", "tčtyři", "tičtyřech", "tičtyřmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tpět", "tipěti", "tipěti", "tpět", "tpět", "tipěti", "tipěti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tšest", "tišesti", "tišesti", "tšest", "tšest", "tišesti", "tišesti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tsedm", "tisedmi", "tisedmi", "tsedm", "tsedm", "tisedmi", "tisedmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tosm", "tiosmi", "tiosmi", "tosm", "tosm", "tiosmi", "tiosmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tdevět", "tidevíti", "tidevíti", "tdevět", "tdevět", "tidevíti", "tidevíti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],

		["f", "-jedna", "jedné", "jedné", "jednu", "jedno", "jedné", "jednou", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["m", "-jeden", "jednoho", "jednomu", "jednoho", "jeden", "jednom", "jedním", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["s", "-jedno", "jednoho", "jednomu", "jednoho", "jedno", "jednom", "jedním", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-dva", "dvou", "dvoum", "dva", "dva", "dvou", "dvěmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-dvě", "dvou", "dvoum", "dva", "dva", "dvou", "dvěmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-tři", "tří", "třem", "tři", "tři", "třech", "třemi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-čtyři", "čtyřech", "čtyřem", "čtyři", "čtyři", "čtyřech", "čtyřmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-pět", "pěti", "pěti", "pět", "pět", "pěti", "pěti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-šest", "šesti", "šesti", "šest", "šest", "šesti", "šesti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-sedm", "sedmi", "sedmi", "sedm", "sedm", "sedmi", "sedmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-osm", "osmi", "osmi", "osm", "osm", "osmi", "osmi", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-devět", "devíti", "devíti", "devět", "devět", "devíti", "devíti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],

		["-", "deset", "deseti", "deseti", "deset", "deset", "deseti", "deseti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],

		["-", "-ná[cs]t", "ná0ti", "ná0ti", "ná0t", "náct", "ná0ti", "ná0ti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],

		["-", "-dvacet", "dvaceti", "dvaceti", "dvacet", "dvacet", "dvaceti", "dvaceti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-třicet", "třiceti", "třiceti", "třicet", "třicet", "třiceti", "třiceti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-čtyřicet", "čtyřiceti", "čtyřiceti", "čtyřicet", "čtyřicet", "čtyřiceti", "čtyřiceti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],
		["-", "-desát", "desáti", "desáti", "desát", "desát", "desáti", "desáti", NULL, NULL, NULL, NULL, NULL, NULL, NULL],

		["m", "-[i]sta", "0sty", "0stovi", "0stu", "0sto", "0stovi", "0stou", "-0sté", "0stů", "0stům", "0sty", "0sté", "0stech", "0sty"],
		["m", "-[o]sta", "0sty", "0stovi", "0stu", "0sto", "0stovi", "0stou", "-0stové", "0stů", "0stům", "0sty", "0sté", "0stech", "0sty"],

		["m", "-předseda", "předsedy", "předsedovi", "předsedu", "předsedo", "předsedovi", "předsedou", "předsedové", "předsedů", "předsedům", "předsedy", "předsedové", "předsedech", "předsedy"],
		["m", "-srdce", "srdce", "srdi", "sdrce", "srdce", "srdci", "srdcem", "srdce", "srdcí", "srdcím", "srdce", "srdce", "srdcích", "srdcemi"],

		// žalobce
		["m", "-[db]ce", "0ce", "0ci", "0ce", "0če", "0ci", "0cem", "0ci/0cové", "0ců", "0cům", "0ce", "0ci/0cové", "0cích", "0ci"],
		// jev
		["m", "-[jň]ev", "0evu", "0evu", "0ev", "0eve", "0evu", "0evem", "-0evy", "0evů", "0evům", "0evy", "0evy", "0evech", "0evy"],
		// lev
		["m", "-[lř]ev", "0evu/0va", "0evu/0vovi", "0ev/0va", "0eve/0ve", "0evu/0vovi", "0evem/0vem", "-0evy/0vové", "0evů/0vů", "0evům/0vům", "0evy/0vy", "0evy/0vové", "0evech/0vech", "0evy/0vy"],
		// vůz
		["m", "-ů[lz]", "o0u/o0a", "o0u/o0ovi", "ů0/o0a", "o0e", "o0u", "o0em", "o-0y/o-0ové", "o0ů", "o0ům", "o0y", "o0y/o0ové", "o0ech", "o0y"],

		["m", "nůž", "nože", "noži", "nůž", "noži", "noži", "nožem", "nože", "nožů", "nožům", "nože", "nože", "nožích", "noži"],

		// clo
		["s", "-[bcčdghksštvzž]lo", "0la", "0lu", "0lo", "0lo", "0lu", "0lem", "-0la", "0el", "0lům", "0la", "0la", "0lech", "0ly"],
		// ramínko
		["s", "-[bcčdnsštvzž]ko", "0ka", "0ku", "0ko", "0ko", "0ku", "0kem", "-0ka", "0ek", "0kům", "0ka", "0ka", "0cích/0kách", "0ky"],
		// okno
		["s", "-[bcčdksštvzž]no", "0na", "0nu", "0no", "0no", "0nu", "0nem", "-0na", "0en", "0nům", "0na", "0na", "0nech/0nách", "0ny"],
		// kolo
		["s", "-o", "a", "u", "o", "o", "u", "em", "-a", "", "ům", "a", "a", "ech", "y"],
		// stavení
		["s", "-í", "í", "í", "í", "í", "í", "ím", "-í", "í", "ím", "í", "í", "ích", "ími"],
		// děvče
		["s", "-[čďť][e]", "10te", "10ti", "10", "10", "10ti", "10tem", "1-ata", "1at", "1atům", "1ata", "1ata", "1atech", "1aty"],
		// veka
		["f", "-[aeiouyáéíóúý]ka", "0ky", "0ce", "0ku", "0ko", "0ce", "0kou", "-0ky", "0k", "0kám", "0ky", "0ky", "0kách", "0kami"],
		// radka
		["f", "-ka", "ky", "ce", "ku", "ko", "ce", "kou", "-ky", "ek", "kám", "ky", "ky", "kách", "kami"],
		// kra
		["f", "-[bdghkmnptvz]ra", "0ry", "0ře", "0ru", "0ro", "0ře", "0rou", "-0ry", "0er", "0rám", "0ry", "0ry", "0rách", "0rami"],
		// dcera
		["f", "-ra", "ry", "ře", "ru", "ro", "ře", "rou", "-ry", "r", "rám", "ry", "ry", "rách", "rami"],
		// lampa
		["f", "-[tdbnvmp]a", "0y", "0ě", "0u", "0o", "0ě", "0ou", "-0y", "0", "0ám", "0y", "0y", "0ách", "0ami"],
		// střecha
		["f", "-cha", "chy", "še", "chu", "cho", "še", "chou", "-chy", "ch", "chám", "chy", "chy", "chách", "chami"],
		// něha
		["f", "-[gh]a", "0y", "ze", "0u", "0o", "ze", "0ou", "-0y", "0", "0ám", "0y", "0y", "0ách", "0ami"],
		// Soňa
		["f", "-ňa", "ni", "ně", "ňou", "ňo", "ni", "ňou", "-ně/ničky", "ň", "ňám", "ně/ničky", "ně/ničky", "ňách", "ňami"],
		// Dáša
		["f", "-[šč]a", "0i", "0e", "0u", "0o", "0e", "0ou", "-0e/0i", "0", "0ám", "0e/0i", "0e/0i", "0ách", "0ami"],
		// žena
		["f", "-a", "y", "e", "u", "o", "e", "ou", "-y", "", "ám", "y", "y", "ách", "ami"],
		// píseň
		["f", "-eň", "ně", "ni", "eň", "ni", "ni", "ní", "-ně", "ní", "ním", "ně", "ně", "ních", "němi"],
		// Třeboň
		["f", "-oň", "oně", "oni", "oň", "oni", "oni", "oní", "-oně", "oní", "oním", "oně", "oně", "oních", "oněmi"],
		// beznaděj
		["f", "-[ě]j", "0je", "0ji", "0j", "0ji", "0ji", "0jí", "-0je", "0jí", "0jím", "0je", "0je", "0jích", "0jemi"],
		// lahev
		["f", "-ev", "ve", "vi", "ev", "vi", "vi", "ví", "-ve", "ví", "vím", "ve", "ve", "vích", "vemi"],
		// kytice
		["f", "-ice", "ice", "ici", "ici", "ice", "ici", "icí", "-ice", "ic", "icím", "ice", "ice", "icích", "icemi"],
		// růže
		["f", "-e", "e", "i", "i", "e", "i", "í", "-e", "í", "ím", "e", "e", "ích", "emi"],
		// epopej
		["f", "-[eaá][jžň]", "10e/10i", "10i", "10", "10i", "10i", "10í", "-10e/10i", "10í", "10ím", "10e", "10e", "10ích", "10emi"],
		// myš
		["f", "-[eayo][š]", "10e/10i", "10i", "10", "10i", "10i", "10í", "10e/10i", "10í", "10ím", "10e", "10e", "10ích", "10emi"],
		// skříň
		["f", "-[íy]ň", "0ně", "0ni", "0ň", "0ni", "0ni", "0ní", "-0ně", "0ní", "0ním", "0ně", "0ně", "0ních", "0němi"],
		// kolegyně
		// TODO verify ňe is ok
		["f", "-[íyý]ňe", "0ně", "0ni", "0ň", "0ni", "0ni", "0ní", "-0ně", "0ní", "0ním", "0ně", "0ně", "0ních", "0němi"],
		// trať
		["f", "-[ťďž]", "0e", "0i", "0", "0i", "0i", "0í", "-0e", "0í", "0ím", "0e", "0e", "0ích", "0emi"],
		// laboratoř
		["f", "-toř", "toře", "toři", "toř", "toři", "toři", "toří", "-toře", "toří", "tořím", "toře", "toře", "tořích", "tořemi"],
		// step
		["f", "-ep", "epi", "epi", "ep", "epi", "epi", "epí", "epi", "epí", "epím", "epi", "epi", "epích", "epmi"],

		// kost
		["f", "-st", "sti", "sti", "st", "sti", "sti", "stí", "-sti", "stí", "stem", "sti", "sti", "stech", "stmi"],

		["f", "ves", "vsi", "vsi", "ves", "vsi", "vsi", "vsí", "vsi", "vsí", "vsem", "vsi", "vsi", "vsech", "vsemi"],

		// Amadeus
		["m", "-[e]us", "0a", "0u/0ovi", "0a", "0e", "0u/0ovi", "0em", "0ové", "0ů", "0ům", "0y", "0ové", "0ích", "0y"],
		// Celsius
		["m", "-[i]us", "0a", "0u/0ovi", "0a", "0e", "0u/0ovi", "0em", "0ové", "0ů", "0ům", "0usy", "0ové", "0ích", "0usy"],
		// Denis
		["m", "-[i]s", "0se", "0su/0sovi", "0se", "0se/0si", "0su/0sovi", "0sem", "0sy/0sové", "0sů", "0sům", "0sy", "0sy/0ové", "0ech", "0sy"],

		["m", "výtrus", "výtrusu", "výtrusu", "výtrus", "výtruse", "výtrusu", "výtrusem", "výtrusy", "výtrusů", "výtrusům", "výtrusy", "výtrusy", "výtrusech", "výtrusy"],
		["m", "trus", "trusu", "trusu", "trus", "truse", "trusu", "trusem", "trusy", "trusů", "trusům", "trusy", "trusy", "trusech", "trusy"],

		// pokus
		["m", "-[aeioumpts][lnmrktp]us", "10u/10a", "10u/10ovi", "10us/10a", "10e", "10u/10ovi", "10em", "10y/10ové", "10ů", "10ům", "10y", "10y/10ové", "10ech", "10y"],
		// útlum
		["s", "-[l]um", "0a", "0u", "0um", "0um", "0u", "0em", "0a", "0", "0ům", "0a", "0a", "0ech", "0y"],
		// publikum
		["s", "-[k]um", "0a", "0u", "0um", "0um", "0u", "0em", "0a", "0", "0ům", "0a", "0a", "0cích", "0y"],
		// medium
		["s", "-[i]um", "0a", "0u", "0um", "0um", "0u", "0em", "0a", "0í", "0ům", "0a", "0a", "0iích", "0y"],
		// rádio
		["s", "-io", "0a", "0u", "0", "0", "0u", "0em", "0a", "0í", "0ům", "0a", "0a", "0iích", "0y"],
		// bar
		["m", "-[aeiouyáéíóúý]r", "0ru/0ra", "0ru/0rovi", "0r/0ra", "0re", "0ru/0rovi", "0rem", "-0ry/-0rové", "0rů", "0rům", "0ry", "0ry/0rové", "0rech", "0ry"],// , array( "m","-[aeiouyáéíóúý]r","0ru/0ra","0ru/0rovi","0r/0ra","0re","0ru/0rovi","0rem",     "-0ry/-0ři","0rů","0rům","0ry","0ry/0ři", "0rech","0ry" )
		// odběr
		["m", "-r", "ru/ra", "ru/rovi", "r/ra", "ře", "ru/rovi", "rem", "-ry/-rové", "rů", "rům", "ry", "ry/rové", "rech", "ry"],// , array( "m","-r",              "ru/ra",  "ru/rovi",  "r/ra",  "ře", "ru/rovi",   "rem",     "-ry/-ři", "rů","rům","ry",    "ry/ři",  "rech", "ry" )
		// kámen
		["m", "-[mnp]en", "0enu/0ena", "0enu/0enovi", "0en/0na", "0ene", "0enu/0enovi", "0enem", "-0eny/0enové", "0enů", "0enům", "0eny", "0eny/0enové", "0enech", "0eny"],
		// hřeben
		["m", "-[bcčdstvz]en", "0nu/0na", "0nu/0novi", "0en/0na", "0ne", "0nu/0novi", "0nem", "-0ny/0nové", "0nů", "0nům", "0ny", "0ny/0nové", "0nech", "0ny"],
		// vtip
		["m", "-[dglmnpbtvzs]", "0u/0a", "0u/0ovi", "0/0a", "0e", "0u/0ovi", "0em", "-0y/0ové", "0ů", "0ům", "0y", "0y/0ové", "0ech", "0y"],
		// reflex
		["m", "-[x]", "0u/0e", "0u/0ovi", "0/0e", "0i", "0u/0ovi", "0em", "-0y/0ové", "0ů", "0ům", "0y", "0y/0ové", "0ech", "0y"],

		["m", "sek", "seku/seka", "seku/sekovi", "sek/seka", "seku", "seku/sekovi", "sekem", "seky/sekové", "seků", "sekům", "seky", "seky/sekové", "secích", "seky"],
		["m", "výsek", "výseku/výseka", "výseku/výsekovi", "výsek/výseka", "výseku", "výseku/výsekovi", "výsekem", "výseky/výsekové", "výseků", "výsekům", "výseky", "výseky/výsekové", "výsecích", "výseky"],
		["m", "zásek", "záseku/záseka", "záseku/zásekovi", "zásek/záseka", "záseku", "záseku/zásekovi", "zásekem", "záseky/zásekové", "záseků", "zásekům", "záseky", "záseky/zásekové", "zásecích", "záseky"],
		["m", "průsek", "průseku/průseka", "průseku/průsekovi", "průsek/průseka", "průseku", "průseku/průsekovi", "průsekem", "průseky/průsekové", "průseků", "výsekům", "průseky", "průseky/průsekové", "průsecích", "průseky"],

		// polibek
		["m", "-[cčšždnňmpbrstvz]ek", "0ku/0ka", "0ku/0kovi", "0ek/0ka", "0ku", "0ku/0kovi", "0kem", "-0ky/0kové", "0ků", "0kům", "0ky", "0ky/0kové", "0cích", "0ky"],
		// tabák
		["m", "-[k]", "0u/0a", "0u/0ovi", "0/0a", "0u", "0u/0ovi", "0em", "-0y/0ové", "0ů", "0ům", "0y", "0y/0ové", "cích", "0y"],
		// prach
		["m", "-ch", "chu/cha", "chu/chovi", "ch/cha", "chu/cha", "chu/chovi", "chem", "-chy/chové", "chů", "chům", "chy", "chy/chové", "ších", "chy"],
		// dosah
		["m", "-[h]", "0u/0a", "0u/0ovi", "0/0a", "0u", "0u/0ovi", "0em", "-0y/0ové", "0ů", "0ům", "0y", "0y/0ové", "zích", "0y"],
		// duben
		["m", "-e[mnz]", "0u/0a", "0u/0ovi", "e0/e0a", "0e", "0u/0ovi", "0em", "-0y/0ové", "0ů", "0ům", "0y", "0y/0ové", "0ech", "0y"],
		// otec
		["m", "-ec", "ce", "ci/covi", "ec/ce", "če", "ci/covi", "cem", "-ce/cové", "ců", "cům", "ce", "ce/cové", "cích", "ci"],
		// učeň
		["m", "-[cčďšňřťž]", "0e", "0i/0ovi", "0e", "0i", "0i/0ovi", "0em", "-0e/0ové", "0ů", "0ům", "0e", "0e/0ové", "0ích", "0i"],
		// boj
		["m", "-oj", "oje", "oji/ojovi", "oj/oje", "oji", "oji/ojovi", "ojem", "-oje/ojové", "ojů", "ojům", "oje", "oje/ojové", "ojích", "oji"],
		// Bláha
		["m", "-[gh]a", "0y", "0ovi", "0u", "0o", "0ovi", "0ou", "0ové", "0ů", "0ům", "0y", "0ové", "zích", "0y"],
		// Rybka
		["m", "-[k]a", "0y", "0ovi", "0u", "0o", "0ovi", "0ou", "0ové", "0ů", "0ům", "0y", "0ové", "cích", "0y"],
		// Hála
		["m", "-a", "y", "ovi", "u", "o", "ovi", "ou", "ové", "ů", "ům", "y", "ové", "ech", "y"],
		// Avril
		["f", "-l", "le", "li", "l", "li", "li", "lí", "le", "lí", "lím", "le", "le", "lích", "lemi"],
		// ???
		["f", "-í", "í", "í", "í", "í", "í", "í", "í", "ích", "ím", "í", "í", "ích", "ími"],
		// beznaděj
		// TODO duplicate?
		["f", "-[jř]", "0e", "0i", "0", "0i", "0i", "0í", "0e", "0í", "0ím", "0e", "0e", "0ích", "0emi"],
		// Třebíč
		["f", "-[č]", "0i", "0i", "0", "0i", "0i", "0í", "0i", "0í", "0ím", "0i", "0i", "0ích", "0mi"],
		// Dobříš
		["f", "-[š]", "0i", "0i", "0", "0i", "0i", "0í", "0i", "0í", "0ím", "0i", "0i", "0ích", "0emi"],
		// Anatolije
		["s", "-[sljřň]e", "0ete", "0eti", "0e", "0e", "0eti", "0etem", "0ata", "0at", "0atům", "0ata", "0ata", "0atech", "0aty"],
		// čaj
		["m", "-j", "je", "ji", "j", "ji", "ji", "jem", "je/jové", "jů", "jům", "je", "je/jové", "jích", "ji"],
		// graf
		["m", "-f", "fa", "fu/fovi", "f/fa", "fe", "fu/fovi", "fem", "fy/fové", "fů", "fům", "fy", "fy/fové", "fech", "fy"],
		// Jiří
		["m", "-í", "ího", "ímu", "ího", "í", "ímu", "ím", "í", "ích", "ím", "í", "í", "ích", "ími"],
		// Hugo
		["m", "-go", "a", "govi", "ga", "ga", "govi", "gem", "gové", "gů", "gům", "gy", "gové", "zích", "gy"],
		// Kvido
		["m", "-o", "a", "ovi", "a", "a", "ovi", "em", "ové", "ů", "ům", "y", "ové", "ech", "y"],
		// šaty
		[NULL, "-[tp]y", NULL, NULL, NULL, NULL, NULL, NULL, "-0y", "0", "0ům", "0y", "0y", "0ech", "0ami"],
		// dřeváky
		[NULL, "-[k]y", NULL, NULL, NULL, NULL, NULL, NULL, "-0y", "e0", "0ám", "0y", "0y", "0ách", "0ami"],
		// ???
		["f", "-ar", "ary", "aře", "ar", "ar", "ar", "ar", "ary", "ar", "arám", "ary", "ary", "arách", "arami"],
		// madam
		["f", "-am", "am", "am", "am", "am", "am", "am", "am", "am", "am", "am", "am", "am", "am"],
		// Jennifer
		["f", "-er", "er", "er", "er", "er", "er", "er", "ery", "er", "erám", "ery", "ery", "erách", "erami"],
		// Joe
		["m", "-oe", "oema", "oemovi", "oema", "oeme", "emovi", "emem", "oemové", "oemů", "oemům", "oemy", "oemové", "oemech", "oemy"],

	];

	/**
	 * Výjimky:
	 * @var array {
	 *  @var string nominative
	 *  @var string replacement
	 *  @var string accusative
	 * }
	 */
	protected $v1 = [
		["osel", "osl", "osla"],
		["karel", "karl", "karla"],
		["karel", "karl", "karla"],
		["pavel", "pavl", "pavla"],
		["pavel", "pavl", "pavla"],
		["havel", "havl", "havla"],
		["havel", "havl", "havla"],
		["bořek", "bořk", "bořka"],
		["bořek", "bořk", "bořka"],
		["luděk", "luďk", "luďka"],
		["luděk", "luďk", "luďka"],
		["pes", "ps", "psa"],
		["pytel", "pytl", "pytel"],
		["ocet", "oct", "octa"],
		["chléb", "chleb", "chleba"],
		["chleba", "chleb", "chleba"],
		["pavel", "pavl", "pavla"],
		["kel", "kl", "kel"],
		["sopel", "sopl", "sopel"],
		["kotel", "kotl", "kotel"],
		["posel", "posl", "posla"],
		["důl", "dol", "důl"],
		["sůl", "sole", "sůl"],
		["vůl", "vol", "vola"],
		["půl", "půle", "půli"],
		["hůl", "hole", "hůl"],
		["stůl", "stol", "stůl"],
		["líh", "lih", "líh"],
		["sníh", "sněh", "sníh"],
		["zář", "záře", "zář"],
		["svatozář", "svatozáře", "svatozář"],
		["kůň", "koň", "koně"],
		["tůň", "tůňe", "tůň"],
		["prsten", "prstýnek", "prstýnku"],
		["smrt", "smrť", "smrt"],
		["vítr", "větr", "vítr"],
		["stupeň", "stupň", "stupeň"],
		["peň", "pň", "peň"],
		["cyklus", "cykl", "cyklus"],
		["dvůr", "dvor", "dvůr"],
		["zeď", "zď", "zeď"],
		["účet", "účt", "účet"],
		["mráz", "mraz", "mráz"],
		["hnůj", "hnoj", "hnůj"],
		["skrýš", "skrýše", "skrýš"],
		["nehet", "neht", "nehet"],
		["veš", "vš", "veš"],
		["déšť", "dešť", "déšť"],
		["myš", "myše", "myš"]
	];

	/**
	 * Group replacements
	 * @TODO remove
	 * @var array
	 */
	protected $aCmpReg = [];


	public function __construct()
	{

		$this->aCmpReg = array_fill(0, 9, ""); // TODO remove

		// TODO move higher
		// $this->v10 - zmena rodu na muzsky
		$this->v10 = ["sleď", "saša", "saša", "dešť", "koň", "chlast", "plast", "termoplast", "vězeň", "sťežeň", "papež", "ďeda", "zeť", "háj", "lanýž", "sluha", "muž", "velmož", "maťej", "maťej", "táta", "kolega", "mluvka", "strejda", "polda", "moula", "šmoula", "slouha", "drákula", "test", "rest", "trest", "arest", "azbest", "ametyst", "chřest", "protest", "kontest", "motorest", "most", "host", "kříž", "stupeň", "peň", "čaj", "prodej", "výdej", "výprodej", "ďej", "zloďej", "žokej", "hranostaj", "dobroďej", "darmoďej", "čaroďej", "koloďej", "sprej", "displej", "aleš", "aleš", "ambrož", "ambrož", "tomáš", "lukáš", "tobiáš", "jiří", "tomáš", "lukáš", "tobiáš", "jiří", "podkoní", "komoří", "jirka", "jirka", "ilja", "ilja", "pepa", "ondřej", "ondřej", "andrej", "andrej", //  "josef",
			"mikuláš", "mikuláš", "mikoláš", "mikoláš", "kvido", "kvido", "hugo", "hugo", "oto", "oto", "otto", "otto", "alexej", "alexej", "ivo", "ivo", "bruno", "bruno", "alois", "alois", "bartoloměj", "bartoloměj", "noe", "noe"];

		// $this->v11 - zmena rodu na zensky
		$this->v11 = ["vš", "dešť", "zteč", "řeč", "křeč", "kleč", "maštal", "vš", "kancelář", "závěj", "zvěř", "sbeř", "neteř", "ves", "rozkoš", // "myša",
			"postel", "prdel", "koudel", "koupel", "ocel", "digestoř", "konzervatoř", "oratoř", "zbroj", "výzbroj", "výstroj", "trofej", "obec", "otep", "miriam", // "miriam",
			"ester", "dagmar"];

		// "transmise,
		// $this->v12 - zmena rodu na stredni
		$this->v12 = ["nemluvňe", "slůně", "kůzle", "sele", "osle", "zvíře", "kuře", "tele", "prase", "house", "vejce",];


		// $this->v0 - nedořešené výjimky
		$this->v0 = ["sten", //      "ester,
			//      "dagmar,
			//      "ovoce,
			//      "zeus,
			//      "zbroj,
			//      "výzbroj,
			//      "výstroj,
			//      "obec,
			//      "konzervatoř,
			//      "digestoř,
			//      "humus,
			//      "muka,
			//      "noe,
			//      "noe,
		];
		//  "miriam,
		//  "miriam,
		// Je Nikola ženské nebo mužské jméno??? (podobně Sáva)
		// $this->v3 - různé odchylky ve skloňování
		//    - časem by bylo vhodné opravit
		$this->v3 = ["jméno", "myš", "vězeň", "sťežeň", "oko", "sole", "šach", "veš", "myš", "klášter", "kněz", "král", "zď", "sto", "smrt", "leden", "len", "les", "únor", "březen", "duben", "květen", "červen", "srpen", "říjen", "pantofel", "žába", "zoja", "zoja", "zoe", "zoe",];

		$this->astrTvar = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", ""];
	}

	public function fasterInflect($text, $animate = FALSE)
	{
		$words = array_reverse(explode(' ', $text));
		$gender = NULL;
		$inflected = [];
		foreach ($words as $word)
		{
			foreach ($this->patterns as $pattern)
			{
				if ($gender && $pattern[0] !== $gender)
				{
					continue;
				}

				$left = $this->match($pattern[1], $word);
				if ($left !== -1)
				{
					$inflectedWord = [1 => $word];
					$prefix = mb_substr($word, 0, $left, 'UTF-8');
					for ($case = 2; $case < 14; $case++)
					{
						$postfix = $pattern[1 + $case];
						foreach ($this->aCmpReg as $i => $replacement)
						{
							$postfix = str_replace($i, $replacement, $postfix);
						}

						$posSlash = mb_strpos($postfix, '/');
						if ($posSlash)
						{
							if ($animate)
							{
								$postfix = mb_substr($postfix, $posSlash + 1);
							}
							else
							{
								$postfix = mb_substr($postfix, 0, $posSlash);
							}
						}

						$inflectedWord[$case] = $prefix . $postfix;
					}
					$inflected[] = $inflectedWord;
					$gender = $pattern[0];
					break;
				}
			}
		}

		return array_reverse($inflected);
	}

	/**
	 * @author Jan Navratil <jan.navratil@heureka.cz>
	 * @param bool $debugMode
	 */
	public function setDebugMode($debugMode)
	{
		$this->isDebugMode = $debugMode;
	}

	//
	//  Fce isShoda vraci index pri shode koncovky (napr. isShoda("-lo","kolo"), isShoda("ko-lo","motovidlo"))
	//  nebo pri rovnosti slov (napr. isShoda("molo","molo").
	//  Jinak je navratova hodnota -1.
	//

	/**
	 * @param string $pattern
	 * @param string $word
	 * @return int position from left where the pattern matched from right otherwise -1
	 */
	protected function match($pattern, $word)
	{
		if (substr($pattern, 0, 1) !== '-')
		{
			return strcmp($pattern, $word) === 0 ? 0 : -1;
		}

		$matches = [];
		$regex = strtr(substr($pattern, 1), ['[' => '([', ']' => '])']);
		if (preg_match('/' . $regex . '$/u', $word, $matches))
		{
			$full = array_shift($matches);
			$i = count($matches) - 1;
			foreach ($matches as $match)
			{
				$this->aCmpReg[$i--] = $match;
			}

			return mb_strlen($word) - mb_strlen($full);
		}

		return -1;
	}

	protected function breakAccents($word)
	{
		return strtr($word, ['di' => 'ďi', 'ti' => 'ťi', 'ni' => 'ňi', 'dě' => 'ďe', 'tě' => 'ťe', 'ně' => 'ňe']);
	}

	protected function fixAccents($word)
	{
		return strtr($word, ['ďi' => 'di', 'ťi' => 'ti', 'ňi' => 'ni', 'ďe' => 'dě', 'ťe' => 'tě', 'ňe' => 'ně']);
	}

	/**
	 * @todo DOCUMENT and refactor
	 * @param $word
	 * @return string
	 */
	protected function CmpFrm($word)
	{
		$CmpFrmRV = "";
		$length = mb_strlen($word, 'UTF-8');
		$txtChar = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
		for ($CmpFrmI = 0; $CmpFrmI < $length; $CmpFrmI++)
		{
			$char = $txtChar[$CmpFrmI];
			if ($char == "0")
			{
				$CmpFrmRV .= $this->aCmpReg[0];
			}
			else
			{
				if ($char == "1")
				{
					$CmpFrmRV .= $this->aCmpReg[1];
				}
				else
				{
					if ($char == "2")
					{
						$CmpFrmRV .= $this->aCmpReg[2];
					}
					else
					{
						$CmpFrmRV .= $char;
					}
				}
			}

		}

		return $CmpFrmRV;
	}

	/**
	 * @param int $case
	 * @param int $patternN
	 * @param string $word
	 * @param bool $animate
	 * @return string
	 */
	protected function inflectWordWithPattern($case, $patternN, $word, $animate = FALSE)
	{
		if (!isset($this->patterns[$patternN]))
		{
			return "?";
		}

		$broken = $this->breakAccents($word);
		$left = $this->match($this->patterns[$patternN][1], $broken);
		if ($left < 0 || $case < 1 || $case > 14) // 8-14 plural
		{
			return "?";
		}

		if ($this->patterns[$patternN][$case] == NULL)
		{
			return NULL;
		}

		if (!$this->isDebugMode && $case == 1) // 1st case not changed
		{
			$rv = $this->fixAccents($broken);
			// this is surely too soon, this should be done only in the end!
		}
		else
		{
			$rv = $this->leftSubstr($left, $broken) . '-' . $this->CmpFrm($this->patterns[$patternN][$case]);
		}

		if ($this->isDebugMode) // skip filtering
		{
			return $rv;
		}

		// animate/inanimate declension
		$posDash = mb_strpos($rv, '-');
		$posSlash = mb_strpos($rv, '/');
		if ($posDash != FALSE && $posSlash != FALSE)
		{
			if ($animate)
			{
				$rv = $this->leftSubstr($posDash, $rv) . $this->rightSubstr($posSlash + 1, $rv, mb_strlen($rv, 'UTF-8'));
			}
			else
			{
				$rv = $this->leftSubstr($posSlash, $rv);
			}
		}

		// clean temp chars
		$broken = strtr($rv, ['-' => '', '/' => '']);

		return $this->fixAccents($broken);
	}

	protected function leftSubstr($n, $txt)
	{
		return mb_substr($txt, 0, $n, 'UTF-8');
	}

	protected function rightSubstr($n, $txt, $length)
	{
		return mb_substr($txt, $n, $length, 'UTF-8');
	}

	/**
	 * @param $text
	 * @param bool $animate
	 * @param string $gender
	 * @return array
	 */
	public function inflect($text, $animate = FALSE, $gender = '')
	{
		$words = array_reverse(explode(' ', $text));

		$this->gender = "0";
		$out = [];
		$wordCount = count($words);
		$astrTvarFirst = $this->astrTvar[0];
		$preferredGender = $this->gender;
		foreach ($words as $i => $word)
		{
			// vysklonovani
			$this->skl2($word, $gender, $animate);

			// vynuceni rodu podle posledniho slova
			if ($i == $wordCount - 1)
			{
				$this->gender = $this->astrTvar[0];
			}

			// pokud nenajdeme $this->vzor tak nesklonujeme
			if (NULL === $this->astrTvar[0] && $i < $wordCount - 1 && $preferredGender != '?')
			{
				for ($j = 1; $j < 15; $j++)
				{
					$this->astrTvar[$j] = $word;
				}
			}

			if ($astrTvarFirst == '?')
			{
				$this->astrTvar[0] = '';
			}

			if ($i < $wordCount)
			{
				for ($j = 1; $j < 15; $j++)
				{
					if (NULL === $this->astrTvar[$j] && !isset($out[$j]))
					{
						$out[$j] = $this->astrTvar[$j];
					}
					else
					{
						$out[$j] = $this->astrTvar[$j] . (isset($out[$j]) ? ' ' . $out[$j] : '');
					}
				}
			}
			else
			{
				for ($j = 1; $j < 15; $j++)
				{
					$out[$j] = $this->astrTvar[$j];
				}
			}
		}

		return $out;
	}

	// Sklonovani podle standardniho seznamu pripon
	private function SklStd($slovo, $ii, $zivotne)
	{
		$cnt = count($this->patterns);
		if ($ii < 0 || $ii > $cnt)
		{
			$this->astrTvar[0] = "!";
		}

		// - seznam nedoresenych slov
		$cnt = count($this->v0);
		for ($jj = 0; $jj < $cnt; $jj++)
		{
			if ($this->match($this->v0[$jj], $slovo) >= 0)
			{
				//str = "Seznam výjimek [" + $jj + "]. "
				//alert(str + "Lituji, toto $slovo zatím neumím správně vyskloňovat.");
				return NULL;
			}
		}

		// nastaveni rodu
		$this->astrTvar[0] = $this->patterns[$ii][0];

		// vlastni sklonovani
		for ($jj = 1; $jj < 15; $jj++)
		{
			$this->astrTvar[$jj] = $this->inflectWordWithPattern($jj, $ii, $slovo, $zivotne);
		}

		// - seznam nepresneho sklonovani
		for ($jj = 0; $jj < count($this->v3); $jj++)
		{
			if ($this->match($this->v3[$jj], $slovo) >= 0)
			{
				//alert("Pozor, v některých pádech nemusí být skloňování tohoto slova přesné.");
				return;
			}
		}

		//  return SklFmt( $this->astrTvar );
	}

	// Pokud je index>=0, je $slovo výjimka ze seznamu "$vx"(v10,...), definovaného výše.
	private function NdxInVx($vx, $slovo)
	{
		$cnt = count($vx);
		for ($vxi = 0; $vxi < $cnt; $vxi++)
		{
			if ($slovo == $vx[$vxi])
			{
				return $vxi;
			}
		}

		return -1;
	}

	// Pokud je index>=0, je $slovo výjimka ze seznamu "$vx", definovaného výše.
	private function ndxV1($slovo)
	{
		foreach ($this->v1 as $i => $v)
		{
			if ($slovo == $v[0])
			{
				return $i;
			}
		}

		return -1;
	}

	// Vybrání vzoru
	private function StdNdx($slovo)
	{
		$char = $this->gender;
		foreach ($this->patterns as $i => $vzor)
		{
			// filtrace rodu
			if ($char != "0" && $char != $vzor[0])
			{
				continue;
			}

			if ($this->match($vzor[1], $slovo) >= 0)
			{
				return $i;
			}
		}

		return -1;
	}

	// Sklonovani podle seznamu vyjimek typu $this->v1
	private function SklV1($slovo, $ii, $zivotne)
	{
		$this->SklStd($this->v1[$ii][1], $this->StdNdx($this->v1[$ii][1]), $zivotne);
		$this->astrTvar[1] = $slovo; //1.p nechame jak je
		$this->astrTvar[4] = $this->v1[$ii][2];
	}

	/**
	 * Nastavuje globalni astrTVar (sklonovane tvary) a PrefRod (vynuceni rodu predchozich slov)
	 */
	private function skl2($slovo, $preferovanyRod = '', $zivotne = FALSE)
	{
		$this->astrTvar = array_fill(0, 16, '');
		$this->astrTvar[0] = "?";

		$firstOriginalChar = mb_substr($slovo, 0, 1, 'UTF-8');
		$slovo = mb_strtolower($slovo, 'UTF-8');

		$flgV1 = $this->ndxV1($slovo);
		if ($flgV1 >= 0)
		{
			$slovoV1 = $slovo;
			$slovo = $this->v1[$flgV1][1];
		}
		//  if( $ii>=0 )
		//  {
		//    $this->astrTvar[1] = "v1: " + $ii;
		//    $this->SklV1( $slovo, $ii );
		//    return SklFmt( $this->astrTvar );
		//    return 0;
		//  }

		$slovo = $this->breakAccents($slovo);

		//$vNdx = 0;

		// Pretypovani rodu?
		$vs = $preferovanyRod;
		if ($vs == "m" || $vs == "f" || $vs == "s")
		{
			$this->gender = $vs;
		}
		else
		{
			$vs = "";
		}


		if ($this->NdxInVx($this->v10, $slovo) >= 0)
		{
			$this->gender = "m";
		}
		else
		{
			if ($this->NdxInVx($this->v11, $slovo) >= 0)
			{
				$this->gender = "f";
			}
			else
			{
				if ($this->NdxInVx($this->v12, $slovo) >= 0)
				{
					$this->gender = "s";
				}
			}
		}

		// Nalezeni $this->vzoru
		$ii = $this->StdNdx($slovo);
		if ($ii < 0)
		{
			//alert("Chyba: proto toto $slovo nebyl nalezen $this->vzor.");
			return -1; //    return "\n  Sorry, nenasel jsem $this->vzor.";
		}

		// Vlastni sklonovani
		$this->SklStd($slovo, $ii, $zivotne);

		if ($flgV1 >= 0)
		{
			$this->astrTvar[1] = $slovoV1; //1.p nechame jak je
			$this->astrTvar[4] = $this->v1[$flgV1][2];
		}

		// Pokud bylo zadané slovo s velkým písmenem na začátku,
		// vrať velké písmeno i ve skloňovaných tvarech
		if (mb_strtoupper($firstOriginalChar) === $firstOriginalChar)
		{
			for ($i = 1; $i <= 15; $i++)
			{
				if ($this->astrTvar[$i])
				{
					$this->astrTvar[$i] = mb_convert_case($this->astrTvar[$i], MB_CASE_TITLE, "UTF-8");
				}
			}
		}

		return 0; //return SklFmt( $this->astrTvar ); //  return "$this->vzor: "+$this->vzor[$ii][1];
	}

	/**
	 * Try to detect feminine genus by given surname
	 * - only basic detection
	 *
	 * @author Jan Navratil <jan.navratil@heureka.cz>
	 * @param $surname
	 * @return null|string
	 */
	public function isFeminineGenusSurname($surname)
	{
		if ('ova' == str_replace('á', 'a', mb_substr(mb_strtolower($surname, 'UTF-8'), -3, 3, 'UTF-8')))
		{
			return 'f';
		}
		else
		{
			return NULL;
		}
	}

}
