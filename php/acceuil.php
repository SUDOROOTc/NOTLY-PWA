<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notly - Mode Offline</title>
<link rel="stylesheet" href="../css/acceuil.css">
<style>
#notesTable { width: 100%; border-collapse: collapse; margin-top: 10px; }
#notesTable th, #notesTable td { border: 1px solid #ccc; padding: 8px; text-align: left; }
#notesTable th { background-color: #f2f2f2; }
#notesTable tr:nth-child(even) { background-color: #fafafa; }
</style>
</head>
<body>

<header>
    <div class="logo">Notly</div>
</header>

<div class="main-container">
    <div class="sidebar">
        <h3>Notes</h3>
        <table id="notesTable">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Produit</th>
                    <th>Fournisseur</th>
                    <th>Prix (XOF)</th>
                </tr>
            </thead>
            <tbody id="notesList"></tbody>
        </table>
    </div>

    <div class="content">
        <h2>Ajouter une note rapidement</h2>
        <form id="noteForm">
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
            </select>

            <label for="produit">Produit</label>
            <select id="produit" name="produit" required>
                <option value="">Sélectionnez un produit</option>
            </select>

            <label for="fournisseur">Numéro du fournisseur</label>
            <input type="text" id="fournisseur" name="fournisseur" placeholder="Numéro ou nom du fournisseur" required>

            <label for="prix">Prix</label>
            <input type="number" id="prix" name="prix" placeholder="Prix en XOF" required>

            <button type="submit">Ajouter la note</button>
        </form>
    </div>
</div>

<script>
let db;

// --- Ouverture IndexedDB ---
const request = indexedDB.open('NotlyDB', 2);

request.onerror = (e) => console.error('Erreur IndexedDB', e);

request.onupgradeneeded = (e) => {
    db = e.target.result;
    if (!db.objectStoreNames.contains('notes')) {
        const store = db.createObjectStore('notes', { keyPath: 'id', autoIncrement: true });
        store.createIndex('categorieId', 'categorieId', { unique: false });
        store.createIndex('produitId', 'produitId', { unique: false });
        store.createIndex('fournisseur', 'fournisseur', { unique: false });
        store.createIndex('prix', 'prix', { unique: false });
    }
};

request.onsuccess = (e) => {
    db = e.target.result;
    console.log("Connexion IndexedDB réussie !");

    // --- Fonctions principales après que DB soit prête ---
    chargerCategories();
    displayNotes();
    syncProduitIdsWithMySQL();
    sendNotesToServerFormData();
};

// --- Charger les catégories ---
function chargerCategories() {
    const selectCategorie = document.getElementById('categorie');
    selectCategorie.innerHTML = '<option value="">Sélectionnez une catégorie</option>';
    if (!db.objectStoreNames.contains('categories')) return;

    const tx = db.transaction(['categories'], 'readonly');
    const store = tx.objectStore('categories');
    const req = store.getAll();
    req.onsuccess = () => {
        req.result.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nom;
            selectCategorie.appendChild(option);
        });
    };
}

// --- Changer la liste des produits selon catégorie ---
document.getElementById('categorie').addEventListener('change', function() {
    const categorieId = parseInt(this.value);
    const selectProduit = document.getElementById('produit');
    selectProduit.innerHTML = '<option value="">Sélectionnez un produit</option>';
    if (!categorieId || !db.objectStoreNames.contains('produits')) return;

    const tx = db.transaction(['produits'], 'readonly');
    const store = tx.objectStore('produits');
    const index = store.index('categorieId');
    const range = IDBKeyRange.only(categorieId);
    const req = index.openCursor(range);
    req.onsuccess = (event) => {
        const cursor = event.target.result;
        if (cursor) {
            const option = document.createElement('option');
            option.value = cursor.value.id;
            option.textContent = cursor.value.nom;
            selectProduit.appendChild(option);
            cursor.continue();
        }
    };
});

// --- Ajouter une note ---
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!db.objectStoreNames.contains('notes')) return;

    const categorieId = parseInt(document.getElementById('categorie').value);
    const produitId = parseInt(document.getElementById('produit').value);
    const fournisseur = document.getElementById('fournisseur').value;
    const prix = parseFloat(document.getElementById('prix').value);

    const tx = db.transaction(['notes'], 'readwrite');
    const store = tx.objectStore('notes');
    store.add({ categorieId, produitId, fournisseur, prix, dateCreation: new Date().toISOString() });

    tx.oncomplete = () => {
        this.reset();
        displayNotes();
    };
});

// --- Afficher les notes ---
function displayNotes() {
    const notesList = document.getElementById('notesList');
    notesList.innerHTML = '';
    if (!db.objectStoreNames.contains('notes')) return;

    const tx = db.transaction(['notes'], 'readonly');
    const store = tx.objectStore('notes');
    const req = store.getAll();
    req.onsuccess = async () => {
        const notes = req.result;
        for (const note of notes) {
            const catNom = await getCategorieNom(note.categorieId);
            const prodNom = await getProduitNom(note.produitId);
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${catNom}</td><td>${prodNom}</td><td>${note.fournisseur}</td><td>${note.prix}</td>`;
            notesList.appendChild(tr);
        }
    };
}

function getCategorieNom(id) {
    return new Promise(resolve => {
        if (!db.objectStoreNames.contains('categories')) return resolve("Inconnu");
        const tx = db.transaction(['categories'], 'readonly');
        const store = tx.objectStore('categories');
        const req = store.get(id);
        req.onsuccess = () => resolve(req.result ? req.result.nom : "Inconnu");
    });
}

function getProduitNom(id) {
    return new Promise(resolve => {
        if (!db.objectStoreNames.contains('produits')) return resolve("Inconnu");
        const tx = db.transaction(['produits'], 'readonly');
        const store = tx.objectStore('produits');
        const req = store.get(id);
        req.onsuccess = () => resolve(req.result ? req.result.nom : "Inconnu");
    });
}

// --- Synchroniser les IDs produits avec MySQL ---
function syncProduitIdsWithMySQL() {
    if (!db || !db.objectStoreNames.contains('notes')) return;

    const tx = db.transaction(['notes'], 'readwrite');
    const store = tx.objectStore('notes');
    const req = store.getAll();
    req.onsuccess = () => {
        const notes = req.result;

        // Map des produits réels MySQL
        const produitMapRaw = {
    // Lot 1 : Animaux et accessoires
    "Bouc (race locale 12-18 mois)": 1,
    "Bouc (race sahel 12-18 mois)": 2,
    "Chèvre (race locale 12-18 mois)": 3,
    "Chèvre (race sahel 12-18 mois)": 4,
    "Bélier (race locale 12-18 mois)": 5,
    "Bélier (race metissé 12-18 mois)": 6,
    "Brébis (race locale 12-18 mois)": 7,
    "Brébis (race metissé 12-18 mois)": 8,
    "Corde d'attache pour animaux": 9,
    "Abreuvoir (bidon 25L)": 10,
    "Mangeoire (bidon 25L)": 11,
    "Vaccination et déparasitage": 12,
    "Son de farine de blé (sac 50 kg)": 13,
    "Tourteau en vrac (sac 50 kg)": 14,
    "Pierre à lécher (1kg)": 15,
    "Arachides coques (sac 100 kg)": 16,
    "Kit hygiène de base": 17,
    "Couvertures": 18,
    "Lampes solaires": 19,
    "Bidons eau potable": 20,
    "Réchauds portables": 21,

    // Lot 2 : Kits couture
    "Kit couture complet": 22,
    "Aiguilles et fils": 23,
    "Ciseaux professionnels": 24,
    "Tissus divers": 25,
    "Machine à coudre (vêtements) Tête blanche simple (mécanique)": 26,
    "Machine à coudre (vêtements) Tête noire simple (mécanique)": 27,
    "Machine à coudre (vêtements) Tête blanche électrique": 28,
    "Machine à coudre (vêtements) Tête blanche électrique (duplicata supprimé)": 29,
    "Ciseaux grand format 6 pouces": 30,
    "Boîte à punaises Métal (boite de 480/500)": 31,
    "Aiguille pour machine Dozen": 32,
    "Aiguille à mains Taille 2": 33,
    "Mètre ruban de 2 m En plastique": 34,
    "Grand fer à repasser Electrique (entre 1200 à 1500 WATT)": 35,
    "Grand fer à repasser Fer a repasser à Charbon": 36,
    "Huile pour machine 1L": 37,
    "Mannequin en plastique plastique pour étaler les habits": 38,
    "Cannettes en coton": 39,
    "Navettes Pièce contenant le fil de la machine à coudre": 40,
    "Rouleau de fil à coudre Paquet": 41,
    "Boutons d'habit Paquet": 42,
    "Fermetures éclaires pour vêtements pour adulte homme Longeur compris entre 12 et 20 cm": 43,
    "Fermetures éclaires pour vêtements pour adulte femme Longeur supérieur ou égal à 50 cm": 44,
    "Popeline noir et blanc 1m": 45,
    "Tissu en coton 1m": 46,
    "Pagne en coton 1 Pièce de 3": 47,

    // Lot 3 : Outils agricoles
    "Binette Trois fourches de type industrielle": 48,
    "Binette deux fourches de type industrielle": 49,
    "Arrosoir En plastique 12L": 50,
    "Machette Type industrielle": 51,
    "Houe Industrielle au format moyen": 52,
    "Daba Type industrielle": 53,
    "Râteau + manche en bois Type industrielle": 54,
    "Pelle + manche en bois Type industrielle": 55,
    "Pioche + manche en bois Type industrielle": 56,
    "Fourche + manche Type industrielle": 57,
    "Engrais Urée de 50kg": 58,
    "Engrais NPK 50kg": 59,
    "Bottes En plastique": 60,
    "Gants de protection En plastique interieur en tissus": 61,
    "Motopompe Eagle à gazoil 150KVA": 62,
    "Grillage Rouleau métallique de 25 m et une hauteur de 1,5m": 63,
    "Poteau métallique de 2m": 64,
    "Tuyaux d'arrosages Tube PVC de 75": 65,
    "Tuyaux d'arrosages Tube gorgé de 75": 66,
    "Coude de 80 PVC": 67,
    "Fil d'attache Rouleau de fil métallique moux": 68,
    "Pulvérisateur de 16l Pompe de type farmjet ou équivalent": 69,
    "Brouettes De type hercule ou Euro ou équivalent": 70,
    "Pesticide en liquide de 250 ml Caiman B19": 71,
    "Pesticide en liquide de 250 ml Emapire": 72,
    "Pesticide bio Limosin, Solsain, Piol, platsain, poudre de seem, HN": 73,
    "Compost /fumure organique 5kg": 74,
    "Compost /fumure organique 25kg": 75,
    "Compost /fumure organique 50kg": 76,

    // Lot 4 : Semences (Piment, Gombo, Aubergine, Tomate, Oseille, Patate, Oignon, Choux, Laitue, Poivron, Concombre, Pastèque, Epinard, Carotte, Maïs, Sorgho, Mil, Niébé, Riz, Arachide, Amarante)
    "Semence de Piment Rouge Variété Papri king/5g": 77,
    "Semence de Piment Rouge Variété Papri king/10g": 78,
    "Semence de Piment Rouge Variété Papri king/50g": 79,
    "Semence de Piment Rouge Variété Papri king/100g": 80,
    "Semence de Piment Rouge Variété Papri king/500g": 81,
    "Semence de Piment Jaune Variété Papri queen/5g": 82,
    "Semence de Piment Jaune Variété Papri queen/10g": 83,
    "Semence de Piment Jaune Variété Papri queen/50g": 84,
    "Semence de Piment Jaune Variété Papri queen/100g": 85,
    "Semence de Piment Jaune Variété Papri queen/500g": 86,
    "Semence de Gombo Vert clair Variété Keleya/5g": 87,
    "Semence de Gombo Vert clair Variété Keleya/10g": 88,
    "Semence de Gombo Vert clair Variété Keleya/50g": 89,
    "Semence de Gombo Vert clair Variété Keleya/100g": 90,
    "Semence de Gombo Vert clair Variété Keleya/500g": 91,
    "Semence d'Aubergine Vert claire Variété FBA 1 (Sissaga)/5g": 92,
    "Semence d'Aubergine Vert claire Variété FBA 1 (Sissaga)/10g": 93,
    "Semence d'Aubergine Vert claire Variété FBA 1 (Sissaga)/50g": 94,
    "Semence d'Aubergine Vert claire Variété FBA 1 (Sissaga)/100g": 95,
    "Semence d'Aubergine Vert claire Variété FBA 1 (Sissaga)/500g": 96,
    "Semence d'Aubergine Violette Variété FBA 3 (Kom silga)/5g": 97,
    "Semence d'Aubergine Violette Variété FBA 3 (Kom silga)/10g": 98,
    "Semence d'Aubergine Violette Variété FBA 3 (Kom silga)/50g": 99,
    "Semence d'Aubergine Violette Variété FBA 3 (Kom silga)/100g": 100,
    "Semence d'Aubergine Violette Variété FBA 3 (Kom silga)/500g": 101,
    "Semence de Tomate Arrondie à allongé Variété FBT 2/5g": 102,
    "Semence de Tomate Arrondie à allongé Variété FBT 2/10g": 103,
    "Semence de Tomate Arrondie à allongé Variété FBT 2/50g": 104,
    "Semence de Tomate Arrondie à allongé Variété FBT 2/100g": 105,
    "Semence de Tomate Arrondie à allongé Variété FBT 2/500g": 106,
    "Semence de Tomate Arrondie Variété FBT 3/5g": 107,
    "Semence de Tomate Arrondie Variété FBT 3/10g": 108,
    "Semence de Tomate Arrondie Variété FBT 3/50g": 109,
    "Semence de Tomate Arrondie Variété FBT 3/100g": 110,
    "Semence de Tomate Arrondie Variété FBT 3/500g": 111,
    "Semence de Tomate Variété Petomech/5g": 112,
    "Semence de Tomate Variété Petomech/10g": 113,
    "Semence de Tomate Variété Petomech/50g": 114,
    "Semence de Tomate Variété Petomech/100g": 115,
    "Semence de Tomate Variété F1 Cobra 26/5g": 116,
    "Semence de Tomate Variété F1 Cobra 26/10g": 117,
    "Semence de Tomate Variété F1 Cobra 26/50g": 118,
    "Semence de Tomate Variété F1 Cobra 26/100g": 119,
    "Semence de Tomate Variété F1 Cobra 26/500g": 120,
    "Semence de Tomate Tropimech/5g": 121,
    "Semence de Tomate Tropimech/10g": 122,
    "Semence de Tomate Tropimech/50g": 123,
    "Semence de Tomate Tropimech/100g": 124,
    "Semence d'Oseille verte Variété R 121/5g": 125,
    "Semence d'Oseille verte Variété R 121/10g": 126,
    "Semence d'Oseille verte Variété R 121/50g": 127,
    "Semence d'Oseille verte Variété R 121/100g": 128,
    "Semence d'Oseille verte Variété R 121/500g": 129,
    "Semence d'Oseille blanc Variété R147-1/5g": 130,
    "Semence d'Oseille blanc Variété R147-1/10g": 131,
    "Semence d'Oseille blanc Variété R147-1/50g": 132,
    "Semence d'Oseille blanc Variété R147-1/100g": 133,
    "Semence d'Oseille blanc Variété R147-1/500g": 134,
    "Semence de Patate douce Rouge Variété BF139/500g": 135,
    "Semence de Patate douce Rouge Variété BF138/500g": 136,
    "Semence d'Oignon Violette Variété12BF/FB01/5g": 137,
    "Semence d'Oignon Violette Variété12BF/FB01/10g": 138,
    "Semence d'Oignon Violette Variété12BF/FB01/50g": 139,
    "Semence d'Oignon Violette Variété12BF/FB01/100g": 140,
    "Semence d'Oignon Violette Variété12BF/FB01/500g": 141,
    "Semence d'Oignon Blanche Variété11BF/5g": 142,
    "Semence d'Oignon Blanche Variété11BF/10g": 143,
    "Semence d'Oignon Blanche Variété11BF/50g": 144,
    "Semence d'Oignon Blanche Variété11BF/100g": 145,
    "Semence d'Oignon Blanche Variété11BF/500g": 146,
    "Semence d'Oignon PREMA/5g": 147,
    "Semence d'Oignon PREMA/10g": 148,
    "Semence d'Oignon PREMA/50g": 149,
    "Semence d'Oignon PREMA/100g": 150,
    "Semence d'Oignon PREMA/500g": 151,
    "Semence de Choux Verte Variété Tropica King F1/5g": 152,
    "Semence de Choux Verte Variété Tropica King F1/10g": 153,
    "Semence de Choux Verte Variété Tropica King F1/50g": 154,
    "Semence de Choux Verte Variété Tropica King F1/100g": 155,
    "Semence de Choux Verte Variété Tropica King F1/500g": 156,
    "Semence de Choux Verte Variété Tropica Kross/5g": 157,
    "Semence de Choux Verte Variété Tropica Kross/10g": 158,
    "Semence de Choux Verte Variété Tropica Kross/50g": 159,
    "Semence de Choux Verte Variété Tropica Kross/100g": 160,
    "Semence de Choux Verte Variété Tropica Kross/500g": 161,
    "Semence de Choux Verte Variété KK Cross/5g": 162,
    "Semence de Choux Verte Variété KK Cross/10g": 163,
    "Semence de Choux Verte Variété KK Cross/50g": 164,
    "Semence de Choux Verte Variété KK Cross/100g": 165,
    "Semence de Choux Verte Variété KK Cross/500g": 166,
    "Semence de Laitue Verte Variété Batavia/5g": 167,
    "Semence de Laitue Verte Variété Batavia/10g": 168,
    "Semence de Laitue Verte Variété Batavia/50g": 169,
    "Semence de Laitue Verte Variété Batavia/100g": 170,
    "Semence de Laitue Verte Variété Batavia/500g": 171,
    "Semence de Poivron Vert Variété Coronado F1/5g": 172,
    "Semence de Poivron Vert Variété Coronado F1/10g": 173,
    "Semence de Poivron Vert Variété Coronado F1/50g": 174,
    "Semence de Poivron Vert Variété Coronado F1/100g": 175,
    "Semence de Poivron Vert Variété Coronado F1/500g": 176,
    "Semence de Concombre NAGANO/5g": 177,
    "Semence de Concombre NAGANO/10g": 178,
    "Semence de Concombre NAGANO/50g": 179,
    "Semence de Concombre NAGANO/100g": 180,
    "Semence de Concombre NAGANO/500g": 181,
    "Semence de Pastèque Kaolac Technisem/5g": 182,
    "Semence de Pastèque Kaolac Technisem/10g": 183,
    "Semence de Pastèque Kaolac Technisem/50g": 184,
    "Semence de Pastèque Kaolac Technisem/100g": 185,
    "Semence de Pastèque Kaolac Technisem/500g": 186,
    "Semence d'Epinard Technisem/5g": 187,
    "Semence d'Epinard Technisem/10g": 188,
    "Semence d'Epinard Technisem/50g": 189,
    "Semence d'Epinard Technisem/100g": 190,
    "Semence d'Epinard Technisem/500g": 191,
    "Semence de Carotte New KARODA/5g": 192,
    "Semence de Carotte New KARODA/10g": 193,
    "Semence de Carotte New KARODA/50g": 194,
    "Semence de Carotte New KARODA/100g": 195,
    "Semence de Carotte New KARODA/500g": 196,
    "Semence de Maïs Variétés FBC 6/1KG": 197,
    "Semence de Maïs Variétés Barka/1KG": 198,
    "Semence de Maïs Variétés Massongo/1KG": 199,
    "Semence de Maïs Variétés KPJ/1KG": 200,
    "Semence de Maïs Variétés KEJ/1KG": 201,
    "Semence de Maïs Variétés Espoir/1KG": 202,
    "Semence de Maïs Variétés Bondofa/1KG": 203,
    "Semence de Sorgho Variétés Sariasso/1KG": 204,
    "Semence de Sorgho Variétés Kpelga/1KG": 205,
    "Semence de Sorgho Variétés Grinkan/1KG": 206,
    "Semence de Sorgho Variétés Gnossiconi/1KG": 207,
    "Semence de Sorgho Variétés Framida/1KG": 208,
    "Semence de Mil Variétés MISARI-2/1KG": 209,
    "Semence de Mil Variétés GB 8735/1KG": 210,
    "Semence de Mil Variétés IBMV 8402/1KG": 211,
    "Semence de Mil Variétés SOSAT C-88/1KG": 212,
    "Semence de Mil Variétés MISARI-1/1KG": 213,
    "Semence de Niébé Telma (niébé vert)/1KG": 214,
    "Semence de Niébé Variété Comcallé/1KG": 215,
    "Semence de Niébé Variété KVx 61-1 (Bengsiido)/1KG": 216,
    "Semence de Niébé Variété KVx 396-4-5-2D/1KG": 217,
    "Semence de Niébé Variété Tiligré/1KG": 218,
    "Semence de Niébé Variété Nafi/1KG": 219,
    "Semence de Riz Variétés FKR33N/1KG": 220,
    "Semence de Riz Variétés FKR19/1KG": 221,
    "Semence de Riz Variétés TS2/1KG": 222,
    "Semence de Riz Variétés FKR 41 à 47 N/1KG": 223,
    "Semence d'Arachide Variétés SH 470 P/1KG": 224,
    "Semence d'Arachide Variétés CN 94 C/1KG": 225,
    "Semence d'Arachide Variétés Fleur 11/1KG": 226,
    "Semence d'Arachide Variétés ICGSE 104/1KG": 227,
    "Semence d'amarante Variété FOTETE/5g": 228,
    "Semence d'amarante Variété FOTETE/10g": 229,
    "Semence d'amarante Variété FOTETE/50g": 230,
    "Semence d'amarante Variété FOTETE/100g": 231,
    "Semence d'amarante Variété FOTETE/500g": 232,
    "Piment BIC SOUM/50g": 233,
    "Piment BIC SOUM/5g": 234,
    "Piment jaune du Burkina/50g": 235,
    "Piment jaune du Burkina/5g": 236,
    "Gombo vert clair Variété INDIANA PLUS/100g": 237,
    "Gombo vert clair Variété INDIANA PLUS/500g": 238,
    "Aubergine Violette Variété KALENDA/50g": 239,
    "Aubergine Violette Variété KALENDA/5g": 240,
    "Tomate Variété Sper Petomech/100g": 241,
    "Tomate Variété Sper Petomech/5g": 242,
    "Tomate Variété COBRA 26/5g": 243,
    "Tomate Variété COBRA 26/100g": 244,
    "Semence d'oseille verte Variété Bissap/50g": 245,
    "Semence d'oseille verte Variété Bissap/5g": 246,
    "Semence d'oignon violette Variété SAFARI/500g": 247,
    "Semence d'oignon violette Variété SAFARI/100g": 248,
    "Semence d'oignon Rouge de TEMA Variété Rouge de TEMA/500g": 249,
    "Semence d'oignon Rouge de TEMA Variété Rouge de TEMA/100g": 250,
    "Semence de LAITUE Variété BATAVIA/5g": 251,
    "Semence de LAITUE Variété BATAVIA/100g": 252,
    "Semence d'Amarante Variété Kaboré/100g": 253,
    "Semence d'Amarante Variété Kaboré/10g": 254,
    // Lot N°9 : Kits Soudure (ID 255 à 287)
    "Poste à souder électrique et accessoires (EDON MMA-257)": 255,
    "Meule de type BOSCH": 256,
    "Grande scie": 257,
    "Petite scie": 258,
    "Lunette de protection pour soudure": 259,
    "Gant de protection en cuir pour soudure": 260,
    "Perçeuse electrique pour les tubes et toles (bosh)": 261,
    "Etabli de soudure et grand étau": 262,
    "Trousse de mèches pour perçeuse en acier (3,5 à 14,5)": 263,
    "Grande cisaille à lame pour soudure": 264,
    "Disque à couper et à meuler moyenne": 265,
    "Tube carré de 30": 266,
    "Tube carré de 25": 267,
    "Z de 40": 268,
    "Fer plat de 30": 269,
    "Fer plat de 20": 270,
    "Fer plat de 10": 271,
    "Lame persienne": 272,
    "IPN": 273,
    "Lime": 274,
    "Antirouille": 275,
    "Pomelle de 80": 276,
    "U ouvert": 277,
    "Baguette pour soudure": 278,
    "Equerre": 279,
    "Pince à souder": 280,
    "Grande scie metallique à souder": 281,
    "Fil de 375 pour poste à souder": 282,
    "Pointeau": 283,
    "Burin": 284,
    "Petite cisaille metallique": 285,
    "Marteau de 5 kg": 286,
    "Chaussure de protection": 287,
    // Lot 10 : Téléphonie / Accessoires (ID 287 à 301)
    "Cable chargeur Type android et type C": 287,
    "Ecouteur simple Avec fil": 288,
    "Ecouteur bluetooth Sans fil": 289,
    "Foureaux Smartphone": 290,
    "Incassable tecno,infinix smartphone": 291,
    "PowerBank 10 000mah Pièce": 292,
    "PowerBank 20 000mah Pièce": 293,
    "Chargeur universel de batterie Tas de 24": 294,
    "Chargeur simple Sachet de 45": 295,
    "Cable avec boitier Pièce": 296,
    "Batterie de téléphone simple (petit téléphone) batterie1000 mAh": 297,
    "Foureaux de petit téléphone En plastique": 298,
    "Téléphone en bouton Ecran 1,52\" pouce Capacité batterie1000 mAh Chargeur, wattscharge standard Autonomie en conversation ll heures (25,8 journdes)": 299,
    "Carte mémoire de téléphone 4GB Carte mémoire pour téléphone": 300,
    "Colle pour plastification de téléphone (01mètre) Films de plastification pour téléphone": 301,
    // Lot 11 : Mécanique (ID 302 à 318)
    "Pompe à vélo à main Pièce": 302,
    "Caisse à outils complet Caisse": 303,
    "Pneu de vélo (homme femme) 28 pouces ou 700mm": 304,
    "Mojau à vélo En aluminium": 305,
    "Chambre à aire pour pneu de 28 pouces": 306,
    "Jante pour pneu de 28 pouces": 307,
    "Rayon pour pneu de 28 pouces": 308,
    "Pièce à colle MF Paquet": 309,
    "Dissolition Paquet": 310,
    "Garre en semi cuire Selle de vélo": 311,
    "Guidon pour vélo homme et dame": 312,
    "Caisse complète de 182 outils pour mécanique automobile Mécanique auto moto": 313,
    "Densimètre/pèse acide pour mesure d'acide de batterie": 314,
    "Sangle à cartouche Métallique de 500mm": 315,
    "Pied à coulisse (150°) 150mm": 316,
    "Parasolaire Pièce": 317,
    "Séringue à huile Plastique de 100ml": 318,
    // Lot 12 : Friperie (ID 319 à 321)
    "Ballot de friperie (Homme sans tache 1er choix) Taille standars": 319,
    "Ballot de friperie (Femme sans tache 1er choix) Taille standars": 320,
    "Ballot de friperie (Enfant sans tache 1er choix) Taille standars": 321

    
};

// --- Correction automatique et normalisation ---
// Supprimer la clé de "duplicata supprimé" si elle existe (fusionnée avec la première occurrence)
const duplicateKeyLabel = "Machine à coudre (vêtements) Tête blanche électrique (duplicata supprimé)";
if (produitMapRaw.hasOwnProperty(duplicateKeyLabel)) {
  delete produitMapRaw[duplicateKeyLabel];
}

// Détecter doublons exacts de clés (rare ici) et conflits d'IDs dans le brut (informative)
const dupKeys = [];
const seenKeys = new Set();
for (const k of Object.keys(produitMapRaw)) {
  if (seenKeys.has(k)) dupKeys.push(k);
  seenKeys.add(k);
}
const idCounts = {};
for (const [k,v] of Object.entries(produitMapRaw)) idCounts[v] = (idCounts[v] || 0) + 1;
const idConflicts = Object.entries(idCounts).filter(([,c]) => c > 1).map(([id]) => Number(id));

if (dupKeys.length) console.warn('Doublons EXACTS de clés trouvés (mêmes clés) :', dupKeys);
if (idConflicts.length) console.warn('Attention — conflits d\'ID trouvés dans le mapping brut (seront renumérotés) :', idConflicts);

// Construire produitMap nettoyé : garder la première occurrence et donner des IDs consécutifs 1..N
const produitMap = (() => {
  const cleaned = {};
  let id = 1;
  for (const key of Object.keys(produitMapRaw)) {
    if (cleaned.hasOwnProperty(key)) continue; // ignore doublons exacts ultérieurs
    cleaned[key] = id++;
  }
  return cleaned;
})();

// Afficher le mapping final (copiable) et le nombre d'éléments
console.log('produitMap normalisé — count =', Object.keys(produitMap).length);
console.log('produitMap (JSON) :\n', JSON.stringify(produitMap, null, 2));

        notes.forEach(note => {
            const nouveauProduitId = produitMap[note.produitNom || note.produit] || note.produitId;
            note.produitId = nouveauProduitId;
            store.put(note);
        });

        console.log("✅ Synchronisation des produitId terminée !");
    };
}

// --- Envoyer les notes au serveur ---
async function sendNotesToServerFormData() {
    console.log("🔄 Début de l'envoi des notes via FormData...");

    const tx = db.transaction("notes", "readonly");
    const store = tx.objectStore("notes");
    const req = store.getAll();

    req.onsuccess = async () => {
        const notesArray = req.result;
        if (!notesArray || notesArray.length === 0) {
            console.warn("⚠️ Aucune note à envoyer.");
            return;
        }

        for (const note of notesArray) {
            const formData = new FormData();
            formData.append('categorieId', note.categorieId);
            formData.append('produitId', note.produitId);
            formData.append('fournisseur', note.fournisseur);
            formData.append('prix', note.prix);
            formData.append('dateCreation', note.dateCreation);

            try {
                const response = await fetch('sync_notes.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.text();
                console.log("Réponse serveur pour cette note :", result);
            } catch (error) {
                console.error("❌ Erreur lors de l'envoi de la note :", error);
            }
        }
    };
}
</script>

</body>
</html>

